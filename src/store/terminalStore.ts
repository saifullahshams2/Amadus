import { create } from 'zustand';
import { WorkArea, WorkAreaState, PNR } from '@/types/amadeus';
import { TrainingLesson } from '@/types/training';
import { TRAINING_MODULES } from '@/lib/curriculum/modules';

export type TerminalTheme = 'modern' | 'crt-green' | 'crt-amber';
export type AppMode = 'training' | 'sandbox';

interface TerminalStore {
  theme: TerminalTheme;
  mode: AppMode;
  activeArea: WorkArea;
  areas: Record<WorkArea, WorkAreaState>;
  
  // Training state
  activeModuleId: string;
  activeLesson: TrainingLesson;
  currentObjectiveIndex: number;
  completedObjectives: string[];
  hintLevel: number; // 0=none, 1=hint1, 2=hint2, 3=solution
  showVisualInspector: boolean;
  
  // Actions
  setTheme: (theme: TerminalTheme) => void;
  setMode: (mode: AppMode) => void;
  setActiveArea: (area: WorkArea) => void;
  selectLesson: (lesson: TrainingLesson) => void;
  nextObjective: () => void;
  incrementHint: () => void;
  resetHint: () => void;
  toggleVisualInspector: () => void;
  
  // Terminal commands
  appendOutput: (area: WorkArea, lines: Array<{ text: string; isInput?: boolean; isError?: boolean; isHeader?: boolean }>) => void;
  clearTerminal: (area: WorkArea) => void;
  updateAreaState: (area: WorkArea, updated: Partial<WorkAreaState>) => void;
}

const initialWorkAreaState = (area: WorkArea): WorkAreaState => ({
  area,
  isSignedIn: true,
  officeId: 'LON1A0980',
  agentId: '1234AB',
  activePnr: null,
  lastAvailabilityDisplay: null,
  history: [],
  historyIndex: -1,
  outputLines: [
    { text: `AMADEUS GDS SIMULATOR - WORK AREA ${area} READY`, isHeader: true },
    { text: `OFFICE: LON1A0980  AGENT: 1234AB  SYSTEM ACTIVE`, isHeader: true },
    { text: `ENTER "HE" OR "HELP" FOR COMMAND REFERENCE`, isHeader: true },
    { text: `------------------------------------------------------------` },
  ],
});

export const useTerminalStore = create<TerminalStore>((set, get) => ({
  theme: 'modern',
  mode: 'training',
  activeArea: 'A',
  areas: {
    A: initialWorkAreaState('A'),
    B: initialWorkAreaState('B'),
    C: initialWorkAreaState('C'),
    D: initialWorkAreaState('D'),
    E: initialWorkAreaState('E'),
    F: initialWorkAreaState('F'),
  },

  activeModuleId: 'mod-1',
  activeLesson: TRAINING_MODULES[0].lessons[0],
  currentObjectiveIndex: 0,
  completedObjectives: [],
  hintLevel: 0,
  showVisualInspector: true,

  setTheme: (theme) => set({ theme }),
  setMode: (mode) => set({ mode }),
  setActiveArea: (area) => set({ activeArea: area }),
  
  selectLesson: (lesson) => set({
    activeLesson: lesson,
    activeModuleId: lesson.moduleId,
    currentObjectiveIndex: 0,
    completedObjectives: [],
    hintLevel: 0,
  }),

  nextObjective: () => set((state) => {
    const nextIdx = state.currentObjectiveIndex + 1;
    const currentObj = state.activeLesson.objectives[state.currentObjectiveIndex];
    const completed = currentObj ? [...state.completedObjectives, currentObj.id] : state.completedObjectives;
    return {
      currentObjectiveIndex: nextIdx,
      completedObjectives: completed,
      hintLevel: 0,
    };
  }),

  incrementHint: () => set((state) => ({ hintLevel: Math.min(state.hintLevel + 1, 3) })),
  resetHint: () => set({ hintLevel: 0 }),
  toggleVisualInspector: () => set((state) => ({ showVisualInspector: !state.showVisualInspector })),

  appendOutput: (area, lines) => set((state) => {
    const current = state.areas[area];
    return {
      areas: {
        ...state.areas,
        [area]: {
          ...current,
          outputLines: [...current.outputLines, ...lines],
        },
      },
    };
  }),

  clearTerminal: (area) => set((state) => ({
    areas: {
      ...state.areas,
      [area]: {
        ...state.areas[area],
        outputLines: [],
      },
    },
  })),

  updateAreaState: (area, updated) => set((state) => ({
    areas: {
      ...state.areas,
      [area]: {
        ...state.areas[area],
        ...updated,
      },
    },
  })),
}));
