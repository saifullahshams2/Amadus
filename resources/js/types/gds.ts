/**
 * Amadeus GDS Training Simulator & Learning Platform
 * Comprehensive TypeScript Interfaces & Domain Schemas
 */

// ==========================================
// 1. User & Role RBAC
// ==========================================

export type Role = 'student' | 'instructor';

export interface User {
    id: number;
    username: string;
    name: string;
    email: string;
    role: Role;
    office_id: string; // e.g. "LON1A0980"
    agent_id: string;  // e.g. "1234AB"
    created_at?: string;
    updated_at?: string;
}

// ==========================================
// 2. Amadeus PNR Structures
// ==========================================

export interface Passenger {
    index: number;
    lastName: string;
    firstName: string;
    title?: string; // MR, MRS, MS, CHD, INF
    type?: 'ADT' | 'CHD' | 'INF';
    dateOfBirth?: string; // For infant/child: DDMMMYY
    infantName?: string;
}

export interface FlightSegment {
    segmentNumber: number;
    airlineCode: string;   // e.g. "BA", "QR"
    flightNumber: string;  // e.g. "178"
    bookingClass: string;  // e.g. "Y", "J", "F"
    departureDate: string; // e.g. "15NOV"
    departureAirport: string; // e.g. "LHR"
    arrivalAirport: string;   // e.g. "JFK"
    statusCode: string;    // e.g. "HK1", "DK1"
    seats: number;
    departureTime: string; // e.g. "1030"
    arrivalTime: string;   // e.g. "1345"
    aircraftEquipment?: string; // e.g. "77W", "359"
}

export interface ContactElement {
    index: number;
    type: 'PHONE' | 'EMAIL';
    detail: string; // e.g. "LON 44-20-7123-4567-A", "JOHN.DOE//GMAIL.COM"
}

export interface TicketingElement {
    raw: string; // e.g. "TL15NOV/LON1A0980", "OK"
    type: 'TL' | 'OK' | 'TTP';
    deadline?: string;
    officeId?: string;
}

export interface FareElement {
    baseFare: number;
    currency: string; // "GBP", "USD", "EUR"
    taxes: Array<{
        code: string;
        amount: number;
    }>;
    totalAmount: number;
    tstNumber?: number;
    isStored: boolean;
}

export interface PNR {
    record_locator: string; // 6-character alpha (e.g. "K7G8X2")
    office_id: string;
    agent_id: string;
    status: 'OPEN' | 'CONFIRMED' | 'TICKETED' | 'CANCELLED';
    creationAgent?: string;
    passengers: Passenger[];
    segments: FlightSegment[];
    contacts: ContactElement[];
    ticketing?: TicketingElement;
    fop?: {
        type: 'CASH' | 'CC' | 'INVOICE';
        details: string;
    };
    receivedFrom?: string;
    remarks?: string[];
    tst?: FareElement;
    eTickets?: Array<{
        ticketNumber: string; // 13-digit standard e.g. "125-2490182901"
        passengerIndex: number;
        status: 'OPEN' | 'FLOWN' | 'EXCHANGED' | 'REFUNDED';
    }>;
    created_at?: string;
    updated_at?: string;
}

// ==========================================
// 3. Scenarios & Practice Drills
// ==========================================

export type ScenarioCategory =
    | 'Round-trip'
    | 'Multi-city'
    | 'Infant-in-lap'
    | 'Schedule Exchange'
    | 'Urgent Ticketing'
    | 'Tariff & Pricing';

export type ScenarioDifficulty = 'Beginner' | 'Intermediate' | 'Advanced';

export interface PassengerProfile {
    names: Array<{
        lastName: string;
        firstName: string;
        title: string;
        type: 'ADT' | 'CHD' | 'INF';
        dob?: string;
    }>;
    contactPhone?: string;
    contactEmail?: string;
    frequentFlyer?: string;
    notes?: string;
}

export interface TargetFlightRequirement {
    origin: string;
    destination: string;
    date: string;
    preferredAirline?: string;
    bookingClass?: string;
}

export interface ValidationCriteria {
    mandatoryPrint: {
        phone: boolean;        // AP / APE element
        receivedFrom: boolean; // RF element
        itinerary: boolean;    // SS flight segments
        name: boolean;         // NM passenger names
        ticketing: boolean;    // TK ticketing element
    };
    minSegments: number;
    requireTst: boolean;       // Must execute FXP
    requireETicket: boolean;   // Must execute TTP
    expectedQueues?: number[]; // Queues involved (e.g. 1, 7)
    specialRemarksPattern?: string;
}

export interface Scenario {
    id: string; // e.g. "SCN-RT-01"
    title: string;
    category: ScenarioCategory;
    description: string;
    difficulty: ScenarioDifficulty;
    passenger_profile: PassengerProfile;
    flight_segments: TargetFlightRequirement[];
    validation_criteria: ValidationCriteria;
    time_limit_minutes: number;
    created_by: string;
    created_at?: string;
    updated_at?: string;
}

// ==========================================
// 4. Submissions & Automated Grading
// ==========================================

export type SubmissionStatus =
    | 'not_started'
    | 'in_progress'
    | 'submitted'
    | 'reviewed';

export interface GradingElementCheck {
    code: string;       // e.g. "NM", "AP", "SS", "TK", "RF", "FXP", "TTP"
    name: string;       // e.g. "Passenger Name Element (NM)"
    passed: boolean;
    pointsAwarded: number;
    maxPoints: number;
    details: string;
}

export interface AutoGradeResult {
    score: number;      // 0 - 100
    maxScore: number;   // 100
    passed: boolean;    // e.g. score >= 70
    checks: GradingElementCheck[];
    summary: string;
    elementsPassedCount: number;
    elementsTotalCount: number;
}

export interface Submission {
    id: string;
    scenario_id: string;
    user_id: string;
    status: SubmissionStatus;
    score: number;
    auto_grade_results?: AutoGradeResult;
    manual_override_score?: number | null;
    instructor_feedback?: string | null;
    terminal_logs?: TerminalLog[];
    pnr_record_locator?: string | null;
    pnr_snapshot?: PNR | null;
    time_elapsed_seconds: number;
    submitted_at?: string | null;
    reviewed_at?: string | null;
    created_at?: string;
    updated_at?: string;
}

// ==========================================
// 5. Simulated Queues
// ==========================================

export type QueueNumber = 1 | 7 | 8 | 9 | 87;

export type QueueCategory =
    | 'TICKETING'
    | 'SCHEDULE_CHANGE'
    | 'WAITLIST'
    | 'GENERAL_MESSAGE'
    | 'EXCHANGE';

export interface QueueItem {
    id: string;
    queue_number: QueueNumber;
    category: QueueCategory;
    office_id: string;
    target_user_id?: string | null;
    pnr_locator: string;
    pnr_summary: {
        passenger: string;
        itinerary: string;
        urgency: 'NORMAL' | 'HIGH' | 'CRITICAL';
    };
    message: string;
    status: 'PENDING' | 'PROCESSED' | 'REMOVED';
    created_at?: string;
    updated_at?: string;
}

export interface QueueSummary {
    queueNumber: QueueNumber;
    category: QueueCategory;
    name: string;
    count: number;
    description: string;
}

// ==========================================
// 6. Terminal Logging, Live Classroom & Spectator
// ==========================================

export type CommandType =
    | 'AVAILABILITY'
    | 'SELL'
    | 'PNR_NAME'
    | 'PNR_CONTACT'
    | 'PNR_TICKETING'
    | 'PNR_RECEIVED'
    | 'PRICING'
    | 'TICKETING'
    | 'QUEUE'
    | 'DECODE'
    | 'GENERAL';

export interface TerminalLog {
    id: string;
    user_id: string;
    command: string;
    output_lines: string[];
    is_error: boolean;
    command_type: CommandType;
    execution_time_ms: number;
    created_at: string;
}

export type ClassroomStudentStatus =
    | 'Online'
    | 'In Terminal'
    | 'Idle'
    | 'Error Loop';

export interface StudentClassroomCard {
    user: User;
    status: ClassroomStudentStatus;
    lastActive: string;
    currentCommand?: string;
    recentErrorRate: number; // percentage
    activePnrLocator?: string;
    totalCommandsExecuted: number;
    completedScenariosCount: number;
}

// ==========================================
// 7. Command Reference & Syntax Validator
// ==========================================

export interface CommandDefinition {
    code: string;
    syntax: string;
    category: 'Availability' | 'PNR Elements' | 'Pricing' | 'Ticketing' | 'Queues';
    description: string;
    example: string;
    commonErrors: string[];
}

export interface SyntaxValidationResult {
    command: string;
    isValid: boolean;
    category?: string;
    explanation: string;
    correctionHint?: string;
    example?: string;
}

export interface LearningAnalytics {
    syntaxAccuracyRate: number;      // e.g. 88.5%
    commandEfficiencyScore: number;  // e.g. 92
    scenariosCompletionRate: number; // e.g. 75%
    totalCommandsExecuted: number;
    successfulCommands: number;
    errorCommands: number;
    recentErrors: Array<{
        command: string;
        timestamp: string;
        errorHint: string;
    }>;
}
