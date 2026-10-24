export type WorkArea = 'A' | 'B' | 'C' | 'D' | 'E' | 'F';

export interface Passenger {
  id: string; // e.g. "1.1", "2.1"
  type: 'ADT' | 'CHD' | 'INF';
  lastName: string;
  firstName: string;
  title?: string; // MR, MRS, MS, MSTR, MISS
  dateOfBirth?: string; // For CHD/INF
  accompaniedBy?: string; // For INF: e.g. "INFANT TO 1.1"
}

export interface FlightSegment {
  segmentNumber: number; // 1, 2, 3...
  airlineCode: string; // "QR", "BA"
  flightNumber: string; // "001", "123"
  bookingClass: string; // "Y", "J", "F", "M", etc.
  departureAirport: string; // "LHR"
  arrivalAirport: string; // "DOH"
  departureDate: string; // "15MAY"
  departureTime: string; // "1455"
  arrivalTime: string; // "2345"
  arrivalDayOffset?: string; // "+1"
  status: 'HK' | 'SS' | 'TK' | 'UN'; // HK = Holding Confirmed, SS = Sold
  seats: number;
  equipment: string; // "77W", "359"
  meal?: string;
  stops?: number;
}

export interface ContactElement {
  id: number;
  type: 'PHONE' | 'EMAIL';
  detail: string; // e.g. "DOH 974-555-1234-A" or "test@agency.com"
}

export interface TicketingElement {
  type: 'TKTL' | 'TKOK' | 'TKXL';
  date?: string;
  officeId?: string;
  raw: string; // e.g. "TKTL15MAY/LON1A0980"
}

export interface FormOfPayment {
  type: 'CASH' | 'CC' | 'CHECK' | 'INVOICE';
  details: string; // e.g. "CCVI4111111111111111/1226" or "CASH"
}

export interface StoredFareTST {
  tstNumber: number; // 1, 2...
  fareBasis: string; // "YIF", "JFF"
  baseFare: number;
  currency: string;
  taxes: Array<{ code: string; amount: number }>;
  total: number;
  passengerIds: string[];
  segments: number[];
  issuedAt?: string;
}

export interface IssuedTicket {
  ticketNumber: string; // 13 digits e.g. "157-2345678901"
  passengerName: string;
  pnrLocator: string;
  issueDate: string;
  officeId: string;
  fop: string;
  totalFare: string;
  coupons: Array<{
    couponNo: number;
    flight: string;
    from: string;
    to: string;
    date: string;
    class: string;
    status: 'O' | 'F' | 'E' | 'V'; // O=Open, F=Flown, E=Exchanged, V=Void
  }>;
}

export interface PNR {
  recordLocator?: string; // 6 chars e.g. "X87TRQ"
  creationOffice: string; // "LON1A0980"
  creationAgent: string; // "1234AB"
  createdAt?: string;
  passengers: Passenger[];
  segments: FlightSegment[];
  contacts: ContactElement[];
  ticketing?: TicketingElement;
  receivedFrom?: string; // e.g. "MR SMITH" or "AGENT"
  remarks: string[];
  specialServiceRequests: Array<{
    type: 'SSR' | 'OSI';
    code: string; // "VGML", "WCHR", "CTCM"
    airline?: string;
    text: string;
    passengerId?: string;
  }>;
  fop?: FormOfPayment;
  commission?: string;
  tsts: StoredFareTST[];
  tickets: IssuedTicket[];
  isLocked?: boolean;
}

export interface FlightInventorySeatClass {
  classCode: string; // "F", "A", "J", "C", "D", "Y", "B", "M", "H", "K", "L", "V"
  availableSeats: number | 'L' | 'C'; // 9, 8... 1, L=Waitlist, C=Closed
  cabin: 'FIRST' | 'BUSINESS' | 'PREMIUM_ECONOMY' | 'ECONOMY';
}

export interface FlightScheduleItem {
  airlineCode: string;
  flightNumber: string;
  departureAirport: string;
  arrivalAirport: string;
  daysOfOperation: string; // "1234567"
  departureTime: string; // "0830"
  arrivalTime: string; // "1215"
  arrivalDayOffset?: string;
  equipment: string;
  stops: number;
  classes: FlightInventorySeatClass[];
}

export interface TerminalLine {
  text: string;
  isInput?: boolean;
  isError?: boolean;
  isHeader?: boolean;
}

export interface WorkAreaState {
  area: WorkArea;
  isSignedIn: boolean;
  officeId: string;
  agentId: string;
  activePnr: PNR | null; // Transient / scratchpad PNR in current work area
  lastAvailabilityDisplay: {
    origin: string;
    destination: string;
    date: string;
    lines: Array<{
      lineNumber: number;
      schedule: FlightScheduleItem;
      departureDate: string;
    }>;
  } | null;
  history: string[]; // Command input history
  historyIndex: number;
  outputLines: TerminalLine[];
}
