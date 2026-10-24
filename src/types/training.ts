import { PNR } from './amadeus';

export interface LessonObjective {
  id: string;
  title: string;
  description: string;
  hint1: string; // Subtle nudge
  hint2: string; // Syntax guide
  solutionCommand: string; // Exact command or answer
  // Evaluates whether this objective has been satisfied
  validate: {
    commandPattern?: string; // Regex test for input command
    expectedOutputContains?: string[];
    pnrCheck?: (pnr: PNR | null) => boolean;
  };
}

export interface TrainingLesson {
  id: string;
  moduleId: string;
  moduleTitle: string;
  lessonNumber: number;
  title: string;
  conceptSummary: string;
  scenarioStory: string;
  keyCommands: Array<{
    syntax: string;
    description: string;
    example: string;
  }>;
  objectives: LessonObjective[];
  starterPnr?: Partial<PNR>;
}

export interface TrainingModule {
  id: string;
  number: number;
  title: string;
  description: string;
  badge: string;
  level: 'Beginner' | 'Intermediate' | 'Advanced';
  lessons: TrainingLesson[];
}

export interface UserProgressRecord {
  userId: string;
  lessonId: string;
  moduleId: string;
  status: 'not_started' | 'in_progress' | 'completed';
  completedObjectives: string[];
  score: number;
  completedAt?: string;
}
