import { NextRequest, NextResponse } from 'next/server';
import { executeCrypticCommand } from '@/lib/amadeus/parser';
import { verifyToken } from '@/lib/auth';
import { initializeDatabase } from '@/lib/db';
import { TRAINING_MODULES } from '@/lib/curriculum/modules';

export async function POST(req: NextRequest) {
  try {
    await initializeDatabase();

    const token = req.cookies.get('gds_token')?.value;
    const session = token ? verifyToken(token) : null;
    const userId = session?.id || 'guest';

    const body = await req.json();
    const { command, state, activeLessonId, currentObjectiveIndex } = body;

    if (!command || typeof command !== 'string') {
      return NextResponse.json({ error: 'Command string is required' }, { status: 400 });
    }

    const execResult = await executeCrypticCommand(command, state, userId);

    // Objective validation for training mode
    let objectiveCompleted = false;
    let nextObjectiveIndex = currentObjectiveIndex;

    if (activeLessonId && typeof currentObjectiveIndex === 'number') {
      let currentLesson = null;
      for (const m of TRAINING_MODULES) {
        const found = m.lessons.find(l => l.id === activeLessonId);
        if (found) {
          currentLesson = found;
          break;
        }
      }

      if (currentLesson && currentLesson.objectives[currentObjectiveIndex]) {
        const obj = currentLesson.objectives[currentObjectiveIndex];
        const v = obj.validate;

        let passed = true;

        // Check command regex pattern
        if (v.commandPattern) {
          const re = new RegExp(v.commandPattern, 'i');
          if (!re.test(command.trim())) {
            passed = false;
          }
        }

        // Check expected output contains
        if (passed && v.expectedOutputContains) {
          const fullOutput = execResult.outputLines.join(' ');
          for (const str of v.expectedOutputContains) {
            if (!fullOutput.toUpperCase().includes(str.toUpperCase())) {
              passed = false;
              break;
            }
          }
        }

        // Check PNR state
        if (passed && v.pnrCheck) {
          try {
            if (!v.pnrCheck(execResult.updatedState.activePnr)) {
              passed = false;
            }
          } catch (e) {
            passed = false;
          }
        }

        if (passed && !execResult.isError) {
          objectiveCompleted = true;
          nextObjectiveIndex = currentObjectiveIndex + 1;
        }
      }
    }

    return NextResponse.json({
      outputLines: execResult.outputLines,
      updatedState: execResult.updatedState,
      isError: execResult.isError,
      commandType: execResult.commandType,
      objectiveCompleted,
      nextObjectiveIndex,
    });
  } catch (err: any) {
    console.error('Terminal execution error:', err);
    return NextResponse.json({
      outputLines: ['SYSTEM ERROR OCCURRED DURING TRANSACTION PROCESSING'],
      isError: true,
      error: err.message,
    }, { status: 500 });
  }
}
