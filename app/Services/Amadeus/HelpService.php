<?php

namespace App\Services\Amadeus;

class HelpResult
{
    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public array $lines,
        public bool $isError = false
    ) {}
}

class HelpService
{
    /**
     * Help topics dictionary mapping normalized topic keys to help text lines.
     *
     * @var array<string, array<int, string>>
     */
    private const HELP_TOPICS = [
        'SIGN' => [
            '--- HELP: SIGN-IN & SIGN-OUT (HE SIGN) ---',
            'JI <SINE>/<DUTY>          : SIGN IN TO CURRENT WORK AREA (E.G. JI 1234AA/AS)',
            'JI <SINE>/<DUTY>-<PASS>   : SIGN IN WITH PASSWORD (E.G. JI 1234AA/AS-PASSWORD)',
            'JI*<SINE>/<DUTY>         : SIGN IN TO ALL WORK AREAS (A-F)',
            'JXC                       : SIGN IN / JUMP EXTENSION TO WORK AREA C',
            'JMB                       : JUMP MOVE TO WORK AREA B',
            'JD                        : JUMP DISPLAY (STATUS OF ALL WORK AREAS)',
            'JB                        : REDISPLAY SIGN MESSAGE FOR THE DAY',
            'JO                        : SIGN OUT FROM CURRENT WORK AREA',
            'JO*                       : SIGN OUT FROM ALL SIGNED WORK AREAS',
            'JOB / JOC                 : SIGN OUT FROM SPECIFIC WORK AREA (B OR C)',
        ],
        'AIS' => [
            '--- HELP: AMADEUS INFORMATION SYSTEM (HE AIS) ---',
            'HE AIS                    : VIEW AIS MAIN MENU',
            'GG AIS                    : PRODUCT ANNOUNCEMENTS IN AMADEUS',
            'GG AIR <CARRIER>          : INFORMATION ON AN AIRLINE (E.G. GG AIR UL, GG AIR LH)',
            'GG AMA IN LCL             : AIRLINE LOCAL CONTACT NUMBERS',
            'GG AMA IN USE             : USEFUL INFORMATION PAGES WITH I N IN AMADEUS',
            'GG AMA IN USE.161         : QUEUE ADDRESSES OF PARTICIPATING AIRLINES',
            'GG COU <COUNTRY>          : INFORMATION ON A PARTICULAR COUNTRY (E.G. GG COU FR)',
            'GG APT <AIRPORT>          : INFORMATION ON A SPECIFIC AIRPORT (E.G. GG APT NRT)',
            'GG WEA <CITY>             : CURRENT WEATHER FORECAST FOR A CITY (E.G. GG WEA LAX)',
            'GG PCA <CARRIER>          : PARTICIPATING CARRIER ACCESS & FUNCTION LEVEL',
            'GG PCAL AIR <A-Z>         : LIST OF CARRIERS SORTED BY AIRLINE NAME',
            'GG PCAL COU <A-Z>         : LIST OF CARRIERS SORTED BY COUNTRY',
            'GG CAR <CODE>             : INFORMATION ON A SPECIFIC CAR COMPANY (E.G. GG CAR ZE)',
            'GG HTL <CODE>             : INFORMATION ON A SPECIFIC HOTEL CHAIN (E.G. GG HTL RD)',
            'GG CODE / GG CODE W       : AMADEUS INPUT AND OUTPUT ACTION CODES',
            'GG ALLIANCE               : AIR ALLIANCES LIST (STAR ALLIANCE, ONEWORLD, SKYTEAM)',
        ],
        'GD' => [
            '--- HELP: OFFICE BRIEFING PAGE (HE GD) ---',
            'HE GD                     : DISPLAY HELP ON OFFICE BRIEFING PAGE',
            'GD                        : DISPLAY YOUR OFFICE BRIEFING PAGE',
            'GMD                       : EDIT / UPDATE OFFICE BRIEFING PAGE',
        ],
        'HE' => [
            '--- HELP: AMADEUS HELP INDEX BY TOPIC (HE HE) ---',
            'HE SIGN   : SIGN-IN AND SIGN-OUT ENTRIES',
            'HE AIS    : AMADEUS INFORMATION SYSTEM PAGES',
            'HE NM     : NAME ELEMENT FORMATS AND MODIFICATIONS',
            'HE AP     : PHONE AND CONTACT ELEMENTS',
            'HE TK     : TICKETING ARRANGEMENT ELEMENTS',
            'HE AN     : NEUTRAL FLIGHT AVAILABILITY',
            'HE SN     : SCHEDULE AND TIMETABLE ENTRIES',
            'HE SS     : SEGMENT SELL (SHORT, LONG, WAITLIST, PASSIVE)',
            'HE RT     : PNR RETRIEVAL AND DISPLAY VARIATIONS',
            'HE RO     : PNR CLAIM AND RESPONSIBILITY TRANSFER',
            'HE XE     : PNR SEGMENT AND ELEMENT CANCELLATION',
            'HE SB     : REBOOKING DATE AND CLASS ENTRIES',
            'HE SP     : SPLIT PNR ENTRIES',
            'HE RH     : PNR HISTORY DISPLAY CODES',
            'HE TI     : TIMATIC (VISA, HEALTH, CUSTOMS)',
            'HE FXA    : BEST PRICER AND FARE OPTIMIZATION',
            'HE PROFILES : CUSTOMER AND COMPANY PROFILES',
            'HE HTL    : HOTEL SYSTEM TRANSACTIONS',
            'HE CAR    : CAR RENTAL RESERVATIONS',
            'TYPE HE <CODE> FOR DETAILED HELP ON ANY TOPIC',
        ],
        'NM' => [
            '--- HELP: NAME ELEMENT (HE NM) ---',
            'NM1<LAST>/<FIRST> <TITLE>           : STANDARD ADULT (E.G. NM1ROY/ASHOK MR)',
            'NM2<LAST>/<P1> <T1>/<P2> <T2>       : MULTIPLE PASSENGERS COMMON SURNAME',
            'NM1<LAST1>/<F1> 1<LAST2>/<F2>       : MULTIPLE DIFFERENT SURNAMES IN ONE ENTRY',
            'NM1<LAST>/<FIRST> (INF/<NM>/<DOB>)   : ACCOMPANIED INFANT WITH SAME SURNAME',
            'NM1<LAST>/<FIRST> (INF<SUR>/<NM>/<DOB>): INFANT WITH DIFFERENT SURNAME',
            'NM1<LAST>/<FIRST> (CHD/<DOB>)       : CHILD PASSENGER WITH DATE OF BIRTH',
            'NM1<LAST>/<FIRST> (INS)             : INFANT REQUIRING A SEAT',
            'NM1<LAST>/<FIRST> (IDSTCR)          : STRETCHER PASSENGER',
        ],
        'AP' => [
            '--- HELP: PHONE & CONTACT FIELD (HE AP) ---',
            'AP                        : USE DEFAULT OFFICE CONTACT FROM PROFILE',
            'AP <CITY> CTCH<NUM>       : HOME CONTACT NUMBER (E.G. AP BOM CTCH45644455)',
            'APM - <CITY> <MOBILE>     : PASSENGER MOBILE NUMBER (E.G. APM - DEL 9818568329)',
            'APE - <EMAIL>             : PASSENGER EMAIL ADDRESS (E.G. APE - ABC@YAHOO.COM)',
            'NOTE: TRANSMIT CONTACT DATA TO AIRLINE UNDER OSI AS WELL (OS <AIR> CTCM...)',
        ],
        'TK' => [
            '--- HELP: TICKETING ARRANGEMENT (HE TK) ---',
            'TKOK                      : TICKETING IN ORDER / ALREADY ISSUED',
            'TKTL<DATE>                : TICKETING TIME LIMIT DATE (E.G. TKTL05OCT)',
            'TKTL<DATE>/<TIME>         : TICKETING TIME LIMIT DATE AND TIME (E.G. TKTL05OCT/1200)',
            'TKXL/<DATE>               : AUTOMATIC CANCELLATION TIME LIMIT (E.G. TKXL/06APR)',
            'TKTL<DATE>/<TIME>/P<PAX>  : PASSENGER-SPECIFIC TIME LIMIT (E.G. TKTL05OCT/1200/P1,4)',
            'TKDO<DATE>                : DOMESTIC ITINERARY (AUTO-QUEUE TO Q8C3)',
            'TKIN<DATE>                : INTERNATIONAL ITINERARY (AUTO-QUEUE TO Q8C5)',
        ],
        'STEPS' => [
            '--- HELP: STEP-BY-STEP RESERVATION WORKFLOW (HE STEPS) ---',
            'STEP 1: CHECK FLIGHT AVAILABILITY -> AN<DATE><ORIG><DEST> (E.G. AN10DECDELLON)',
            'STEP 2: SELL SEATS -> SS<SEATS><CLASS><LINE> (E.G. SS1Y3)',
            'STEP 3: ENTER PASSENGER NAME -> NM1<SURNAME>/<FIRSTNAME> <TITLE>',
            'STEP 4: ENTER CONTACT PHONE -> AP <CITY> <PHONE> / APE - <EMAIL>',
            'STEP 5: ENTER TICKETING ARRANGEMENT -> TKTL<DATE> OR TKOK',
            'STEP 6: ENTER RECEIVED FROM -> RF <PASSENGER OR AGENT>',
            'STEP 7: END TRANSACTION -> ET (FILE & CLOSE) OR ER (FILE & REDISPLAY)',
        ],
        'MEAL CODES' => [
            '--- HELP: SPECIAL MEAL CODES (HE MEAL CODES) ---',
            'AVML : ASIAN VEGETARIAN MEAL       BBML : BABY MEAL',
            'BLML : BLAND MEAL                  CHML : CHILD MEAL',
            'DBML : DIABETIC MEAL               FPML : FRUIT PLATTER',
            'GFML : GLUTEN FREE MEAL            HNML : HINDU MEAL',
            'KSML : KOSHER MEAL                 LCML : LOW CALORIE MEAL',
            'LFML : LOW FAT MEAL                LSML : LOW SODIUM MEAL',
            'MOML : MUSLIM MEAL                 NLML : LOW LACTOSE MEAL',
            'ORML : ORIENTAL MEAL               SFML : SEAFOOD MEAL',
            'VGML : VEGAN (STRICT VEGETARIAN)   VLML : VEGETARIAN LACTO-OVO',
            'ENTRY: SR <MEALCODE> (E.G. SR VGML / SR MOML/P1)',
        ],
        'TLA' => [
            '--- HELP: TICKETLESS AIRLINE ACCESS (HE TLA) ---',
            '1. CHECK AVAILABILITY   : AN<DATE><ORIG><DEST>/A<AIRLINE> (E.G. AN12JUNTRZKUL/AAK)',
            '2. CHANGE TO TICKETLESS : ACW1/B1 (LINE 1, SEATS 1)',
            '3. SELL SEATS           : SS1V1',
            '4. MANDATORY ELEMENTS   : NM, AP, APE, TKOK',
            '5. PRICE ITINERARY      : FXP',
            '6. FORM OF PAYMENT      : FP CASH OR FP CCVI4111111111111111/1225',
            '7. END TRANSACTION      : RF AGENT; ER',
            '8. SYNCHRONISE BOOKING  : RTLC',
        ],
        'TI' => [
            '--- HELP: TIMATIC TRAVEL INFORMATION (HE TI) ---',
            'TIFV                      : MASK FOR VISA INFORMATION',
            'TIFH                      : MASK FOR HEALTH INFORMATION',
            'TIFA                      : MASK FOR VISA AND HEALTH INFORMATION',
            'TIRV/NAIN/S2-3            : REFERENCE TIMATIC FROM PNR (NATIONALITY IN, SEGS 2-3)',
            'TINEWS                    : INDEX FOR NEWS ITEMS',
            'TIRULES                   : RULES, TERMS AND DEFINITIONS',
            'TILCC/<COUNTRY>           : LIST OF AIRPORTS FOR SPECIFIC COUNTRY',
            'TIDFT/<COUNTRY>/CS        : CHECK CUSTOMS REGULATIONS',
            'TIRGL                     : LIST OF COUNTRY GROUP CODES',
            'TIRGL/SCHS                : SCHENGEN STATES MEMBER COUNTRIES',
        ],
        'DD' => [
            '--- HELP: DATE & TIME CALCULATIONS (HE DD) ---',
            'DD <CITY>                 : CURRENT LOCAL TIME IN CITY (E.G. DD SIN)',
            'DD <CITY1>/<CITY2>        : TIME DIFFERENCE BETWEEN TWO CITIES (E.G. DD LON/LAX)',
            'DD ZZZ<TIME>/<CITY>       : CONVERT UTC TIME TO LOCAL TIME (E.G. DD ZZZ1500/DEL)',
            'DD<DATE>                  : DAY OF WEEK FOR SPECIFIC DATE (E.G. DD25DEC08)',
            'D<DATE>/<DAYS>            : DATE OFFSET CALCULATION (E.G. D22APR13/30)',
        ],
        'AN' => [
            '--- HELP: NEUTRAL AVAILABILITY (HE AN) ---',
            'AN<DATE><ORIG><DEST>      : BASIC AVAILABILITY (E.G. AN10DECDELLON)',
            'AN<DATE><ORIG><DEST>/A<AL>: SPECIFIC AIRLINE (E.G. AN10DECDELSIN/ASQ)',
            'AN<DATE><ORIG><DEST>/A<L1>,<L2>: MULTIPLE AIRLINES (UP TO 6)',
            'AN<DATE><ORIG><DEST>/A-<L>: EXCLUDE AIRLINE (E.G. AN23DECDELLON/A-AI,IT)',
            'AN<DATE><ORIG><DEST>/C<CLS>: CABIN CLASS / RBD (E.G. AN20DECDELLON/ABA/KY)',
            'AN<DATE><ORIG><DEST>/B<NUM>: NUMBER OF SEATS REQUIRED (E.G. AN23DECDELLON/B4)',
            'AN<DATE><ORIG><DEST>/X<VIA>: SPECIFIC TRANSIT POINT (E.G. AN28JUNDELLAX/XBKK)',
            'AN<DATE><ORIG><DEST>/X-<VIA>: EXCLUDE CONNECTING POINT (E.G. AN19SEPDELLON/X-FRA)',
            'AN<DATE><ORIG><DEST>/O    : ONLINE DIRECT CONNECTIONS ONLY',
            'AN*A / AN*O / AN*S        : ALLIANCE SEARCH (*A=STAR, *O=ONEWORLD, *S=SKYTEAM)',
            'AA<DATE><ORIG><DEST><TIME>: SEARCH BY ARRIVAL TIME (E.G. AA12DECLONNYC1500)',
            'AN/<DATE><ORIG><DEST>/A<AL>: 7-DAY SEARCH WINDOW (E.G. AN/17DECBOMNBO/AKQ)',
        ],
        'DIR' => [
            '--- HELP: DIRECT ACCESS (HE DIR) ---',
            'HE DIR <CARRIER>          : VERIFY DIRECT ACCESS FUNCTIONS (E.G. HE DIR 9W)',
            '1<AL>AD<DATE><ORIG><DEST> : DIRECT ACCESS AVAILABILITY (E.G. 19WAD23DECSINJKT)',
            '1<AL> MN / MY / MD / MU   : SCROLL DIRECT ACCESS AVAILABILITY',
            '1<AL>RT <RLOC>            : DIRECT ACCESS PNR RETRIEVAL (E.G. 1AIRT H55PW)',
            '1<AL>FQD<ORIG><DEST>      : DIRECT ACCESS FARE DISPLAY (E.G. 19WFQDDELLON)',
        ],
        'TN' => [
            '--- HELP: TIMETABLE ENTRIES (HE TN) ---',
            'TN<ORIG><DEST>            : NEUTRAL TIMETABLE (E.G. TNDELSIN)',
            'TN <ORIG><DEST>/A<AL>     : TIMETABLE WITH AIRLINE (E.G. TN DELPBH/AKB)',
            'TN<DATE><ORIG><DEST>/A<AL>: TIMETABLE WITH DATE AND AIRLINE',
        ],
        'SN' => [
            '--- HELP: SCHEDULE ENTRIES (HE SN) ---',
            'SN<ORIG><DEST>            : NEUTRAL SCHEDULE DISPLAY',
            'SN<DATE><ORIG><DEST>/A<AL>: SCHEDULE WITH DATE AND AIRLINE (E.G. SN15DECCCUPBH/AKB)',
            'SN<DATE><RT1>*<RT2>       : ROUND TRIP SCHEDULE (E.G. SN23SEPMADLIS*BCNCDG)',
        ],
        'AC' => [
            '--- HELP: AVAILABILITY CHANGE ENTRIES (HE AC / HE ACR) ---',
            'AC <DATE>                 : CHANGE DATE (E.G. AC 15DEC)',
            'AC <+DAYS> / AC <-DAYS>   : CHANGE DAYS FORWARD/BACKWARD (E.G. AC 5, AC -3)',
            'AC/A<CARRIER>             : CHANGE AIRLINE (E.G. AC/ABA, AC/AYY)',
            'AC FQ                     : CONVERT AVAILABILITY TO FARE DISPLAY',
            'AC SN                     : CONVERT TO SCHEDULE DISPLAY',
            'AC <ORIG>                 : CHANGE ORIGIN CITY (E.G. AC BOM)',
            'AC // <DEST>              : CHANGE DESTINATION CITY (E.G. AC // KUL)',
            'ACR<DATE>                 : CHANGE RETURN DATE (E.G. ACR20DEC)',
            'ACR<DATE>/A<CARRIER>      : CHANGE RETURN DATE AND AIRLINE (E.G. ACR20DEC/ABA)',
            'ACL<LINE>                 : CHANGE TO DIRECT ACCESS DISPLAY (E.G. ACL1)',
        ],
        'DO' => [
            '--- HELP: FLIGHT INFORMATION (HE DO) ---',
            'DO <AIR><FLT> /<DATE> /<SECTOR> : DISPLAY FLIGHT INFO (E.G. DO TG774 /23DEC /BKKLAX)',
            'DO <SEGMENT>              : FLIGHT INFO FROM PNR SEGMENT (E.G. DO 2)',
            'DO <LINE>                 : FLIGHT INFO FROM AVAILABILITY DISPLAY (E.G. DO 21)',
        ],
        'DM' => [
            '--- HELP: MINIMUM CONNECTING TIME (HE DM) ---',
            'DM <CITY>                 : MCT FOR CITY/AIRPORT (E.G. DM FRA)',
            'DM <APT1>-<APT2>          : INTER-AIRPORT MCT WITH TERMINALS (E.G. DM ORYW-CDG2B)',
            'DM <AIR1>/ <APT> /<AIR2>  : MCT BETWEEN CARRIERS (E.G. DM BA/ LHR /AA)',
            'DM <LINE>                 : MCT FOR LINE FROM AVAILABILITY DISPLAY',
            'DM <SEG1>/<SEG2>          : MCT BETWEEN PNR SEGMENTS (E.G. DM 2/4)',
            'DMI                       : CHECK MCT AND SEGMENT CONTINUITY IN ACTIVE PNR',
        ],
        'DRT' => [
            '--- HELP: DISPLAY CONNECTION POINTS (HE DRT) ---',
            'DRT <ORIG> <DEST>         : NEUTRAL CONNECTING POINTS (E.G. DRT DEL BJS)',
            'DRT <DATE> <PAIR> / A<AL> : WITH DATE AND SPECIFIC AIRLINE',
            'DRT <DATE> <PAIR> / A-<AL>: WITH SPECIFIC AIRLINE EXCLUDED',
            'DRT <DATE> <PAIR> / X<VIA>: WITH SPECIFIC CONNECT POINT OPTION',
            'DRT <AL> <DATE> <PAIR>    : CARRIER PREFERRED CONNECT POINTS',
        ],
        'SS' => [
            '--- HELP: SEGMENT SELL (HE SS) ---',
            'SS<SEATS><CLS><LINE>      : SELL SEATS FROM AVAILABILITY (E.G. SS1Y3, SS2C1)',
            'SS<SEATS><CLS><LINE1>*<LINE2>: SELL DUAL CITY PAIR (E.G. SS2Y1*11, SS1Y1*F13)',
            'SS<SEATS><CLS><LINE>/PE   : SHORT SELL WAITLISTED SEGMENT (E.G. SS1Y1/PE)',
            'S<AIR><FLT><CLS><DATE><PAIR><N>: LONG SELL FLIGHT (E.G. STG316M03AUGDELBKK1)',
            'SS<SEATS><CLS><LINE>/P<N> : SELL SEATS WITH PASSENGER ASSOCIATION',
            'SS<SEATS><CLS><LINE> /UM<AGE>: SELL SEATS FOR UNACCOMPANIED MINOR (UMNR)',
            'SS<SEATS><CLS><LINE>/ST   : SELL SPACE FOR STRETCHER PASSENGER',
        ],
        'AB' => [
            '--- HELP: BILLING & MAILING ADDRESSES (HE AB / HE AM) ---',
            'AB//CY-<COMPANY>/A1-<ADDR1>/CI-<CITY>/ZP-<ZIP>/CO-<COUNTRY> : BILLING ADDRESS',
            'AM//CY-<COMPANY>/A1-<ADDR1>/CI-<CITY>/ZP-<ZIP>/CO-<COUNTRY> : MAILING ADDRESS',
            'EXAMPLE: AB//CY-AMADEUS/A1-156 SECTOR 24/CI-NEWDELHI/ZP-110070/CO-INDIA',
        ],
        'RM' => [
            '--- HELP: REMARKS (HE RM / HE RC / HE RIR) ---',
            'RM <TEXT>                 : GENERAL REMARK (E.G. RM PAX WILL COLLECT TICKETS)',
            'RC <TEXT>                 : CONFIDENTIAL REMARK (E.G. RC PAX PAYS BY CC)',
            'RIR <TEXT>                : ITINERARY REMARK (E.G. RIR HAVE A PLEASANT FLIGHT)',
            'OP<DATE>/<TEXT>           : OPTION ELEMENT AUTO QUEUE (E.G. OP12DEC/US VISA)',
        ],
        'OSI' => [
            '--- HELP: OTHER SERVICE INFORMATION (HE OSI) ---',
            'OS <AIR> <TEXT>           : TRANSMIT INFORMATION TO AIRLINE',
            'OS EK PAX DEL CTCH 57775777   : CONTACT NUMBER TRANSMISSION',
            'OS EK PAX CIP MD ASIA TYRES/P1: CIP PASSENGER DESIGNATION',
            'OS EK TIMELIMIT 12DEC 1200HRS : TICKETING TIME LIMIT NOTIFICATION',
            'OS YY PAX CTCH DEL 5191912    : NOTIFICATION TO ALL AIRLINES IN PNR',
            'OS BA CTCM DEL 9818568329     : PASSENGER MOBILE TRANSMISSION',
        ],
        'SM' => [
            '--- HELP: ADVANCE SEAT MAPS (HE SM) ---',
            'SM <AIR><FLT>/<CLS>/<DATE><PAIR> : SEAT MAP FOR SPECIFIC FLIGHT (E.G. SM LH761/C/03SEPDELFRA)',
            'SM<SEGMENT>               : SEAT MAP FOR PNR SEGMENT (E.G. SM4)',
            'ST/R/<SEAT>               : REQUEST SPECIFIC SEAT (E.G. ST/R/14B)',
            'ST/W / ST/A / ST/B        : PREFERENCE: WINDOW, AISLE, BULKHEAD',
        ],
        'SO' => [
            '--- HELP: OPEN SEGMENT (HE SO) ---',
            'SO <AIR> <CLS> <PAIR>     : OPEN SEGMENT FOR AIRLINE (E.G. SOLHCFRABOM)',
            'SO <AIR> <CLS> <DATE><PAIR>: OPEN SEGMENT WITH DATE (E.G. SOLHC24DECFRABOM)',
            'SO YY <CLS> <PAIR>        : OPEN SEGMENT NON-SPECIFIC CARRIER (E.G. SOYYCFRABOM)',
        ],
        'SI' => [
            '--- HELP: INFORMATION SEGMENT (HE SI) ---',
            'SI ARNK / SIARNK          : SURFACE SEGMENT (BREAK IN CONTINUITY)',
            'SI<AIR><FLT><CLS><DATE><PAIR>HK<N>/<TIME> : INFORMATION FLIGHT SEGMENT',
            'EXAMPLE: SISV219Y20MAYJEDISTHK1/0815 1645',
        ],
        'FARE DIAGNOSTIC' => [
            '--- HELP: FARE DIAGNOSTIC (HE FARE DIAGNOSTIC) ---',
            'FXX/D F-<FAREBASIS>       : DIAGNOSE SPECIFIC FARE BASIS IN PRICING',
            'FQPLONSINTYO/S/DF-<FB>    : PRICING WITH SPECIFIC FARE DIAGNOSTIC',
            'EXAMPLE: FXX/D F-Y2RT',
        ],
        'FFN' => [
            '--- HELP: FREQUENT FLYER NUMBER (HE FFN) ---',
            'FFN<AIR>-<NUM>            : ADD FREQUENT FLYER NUMBER TO PNR',
            'FFN<AIR>-<NUM>/P<PAX>     : ADD FF NUMBER FOR SPECIFIC PASSENGER',
            'FFA<AIR>-<NUM>            : CREATE PNR DIRECTLY FROM FF DATABASE',
            'FFD<AIR>-<NUM>            : DISPLAY FREQUENT FLYER NAME FROM PROFILE',
            'FFD <AIR>                 : CHECK FF AGREEMENTS FOR AIRLINE',
            'FFN<AIR>-<NUM>,<P1>,<P2>  : PASS FF NUMBER TO PARTNER CARRIERS',
            'SR FQTV <AIR>-<NUM>/P<PAX>: SSR ENTRY FOR FREQUENT FLYER',
        ],
        'ES' => [
            '--- HELP: EXTENDED SECURITY (HE ES) ---',
            'ES <OFFICE>-R             : SET READ-ONLY SECURITY AGREEMENT',
            'ES <OFFICE>-B             : SET READ AND WRITE SECURITY',
            'ES <OFFICE>-T             : SET READ AND TICKET SECURITY',
            'ESD                       : CANCEL EXTENDED SECURITY AGREEMENT',
            'ESX RT/<OFFICE>-<NAME>    : RETRIEVE PNR UNDER ES AGREEMENT',
            'RTE                       : RETRIEVE SECURITY ELEMENT FOR CURRENT PNR',
            'RHE                       : RETRIEVE SECURITY ELEMENT HISTORY',
        ],
        'FH' => [
            '--- HELP: TICKET NUMBER TRANSMISSION (HE FH / HE OS) ---',
            'FHM <TICKET>              : MANUAL TICKET NUMBER TRANSMISSION',
            'FHA <TICKET>              : AUTOMATED TICKET NUMBER TRANSMISSION',
            'FHE <TICKET>              : ELECTRONIC TICKET NUMBER TRANSMISSION',
            'FHE INF <TICKET>          : ELECTRONIC TICKET FOR INFANT',
            'FHE <TICKET>/P<PAX>       : PASSENGER-ASSOCIATED TICKET',
            'FHE <TKT>-<TKT2>/P<PAX>   : CONJUNCTION TICKETS TRANSMISSION',
        ],
        'RT' => [
            '--- HELP: PNR RETRIEVAL & DISPLAY (HE RT) ---',
            'RT<RECORDLOCATOR>         : RETRIEVE BY 6-CHARACTER AMADEUS RECORD LOCATOR',
            'RT/<SURNAME>              : RETRIEVE BY PASSENGER SURNAME',
            'RT/<SURNAME>/<INITIAL>    : RETRIEVE BY SURNAME AND INITIAL (E.G. RT/SINGH/J)',
            'RT/<SURNAME>/<INIT>*A     : RETRIEVE ACTIVE PNRS ONLY',
            'RT<LINE>                  : RETRIEVE PNR AT LINE NUMBER FROM SIMILAR LIST',
            'RT0                       : RETURN TO SIMILAR LIST DISPLAY',
            'RT<AIR><FLT>/<DATE><PAIR>-<NAME> : RETRIEVE BY FLIGHT (E.G. RTLH761/14SEPDELFRA-SEN)',
            'RTTKT/<TICKETNUMBER>      : RETRIEVE BY TICKET NUMBER (E.G. RTTKT/016-1234567890)',
            'RT                        : REDISPLAY CURRENT ACTIVE PNR',
            'RTI                       : DISPLAY ITINERARY SEGMENTS ONLY',
            'RTG                       : DISPLAY GENERAL FACTS (OSI/SSR ELEMENTS)',
            'RTR                       : DISPLAY REMARKS ONLY',
            'RTF,K,I                   : DISPLAY FARE, TICKETING AND ITINERARY ONLY',
            'RT/P<PAX>                 : DISPLAY ELEMENTS FOR SPECIFIC PASSENGER ONLY',
            'RL                        : DISPLAY AIRLINE RECORD LOCATORS',
            'RLN / RLN <NUM>           : DISPLAY / RETRIEVE BY NUMERIC RECORD LOCATOR',
            'RT <NUMERICLOCATOR>       : RETRIEVE BY 12-DIGIT NUMERIC LOCATOR',
            'RTSVI<SEGMENT>            : DISPLAY FLIGHT INFORMATION FROM RETRIEVED PNR',
            'RTU                       : RETRIEVE PNR FROM CUSTOMER PROFILE',
            'RT*E                      : RETRIEVE PNR FROM DISPLAYED E-TICKET RECORD',
            'RTQ                       : DISPLAY ALL QUEUES WHERE PNR IS PLACED',
            'RTLC                      : SYNCHRONISE TICKETLESS BOOKING',
        ],
        'RO' => [
            '--- HELP: PNR CLAIM (HE RO) ---',
            'RO <AIR> <AIRLINELOCATOR> : RETRIEVAL BY AIRLINE RECORD LOCATOR (E.G. RO CO ABC123)',
            'RO <AIR> <FLT>/<DATE><PAIR>-<NAME> : RETRIEVAL BY FLIGHT, DATE AND NAME',
            'RO <LINE>                 : SELECT FROM SIMILAR NAME LIST',
            'RO 0                      : RETURN TO SIMILAR NAME LIST',
            'ER / ET                   : END TRANSACTION TO CLAIM RESPONSIBILITY',
        ],
        'XE' => [
            '--- HELP: PNR MODIFICATIONS (HE XE / HE XI / HE DL / HE SB) ---',
            'XE <SEGMENT>              : CANCEL SPECIFIED SEGMENT (E.G. XE 4)',
            'XE <SEG1>-<SEG2>          : CANCEL RANGE OF SEGMENTS (E.G. XE 4-8)',
            'XE <SEG1>,<SEG2>          : CANCEL NON-CONSECUTIVE SEGMENTS (E.G. XE 4,6)',
            'XI                        : CANCEL ENTIRE ITINERARY',
            'DL<ELEMENT>               : DELETE ELEMENT (E.G. REMOVE INACTIVE STATUS HX/UC)',
            '<LINE>/HK                 : ACCEPT STATUS UPDATE AS HK (E.G. 8/HK)',
            '<LINE>/P<P1>,<P2>         : ADD OR CHANGE PASSENGER ASSOCIATION (E.G. 7/P1,4)',
            'SB<CLS><SEG>              : REBOOK CLASS FOR SEGMENT (E.G. SB6, SBY6)',
            'SB<DATE><SEG>             : REBOOK DATE FOR SEGMENT (E.G. SB25DEC7)',
            'SBY                       : REBOOK CLASS FOR ALL ITINERARY SEGMENTS',
            'ERK / ETK                 : END TRANSACTION & RETRIEVE / IGNORE',
        ],
        'IED' => [
            '--- HELP: PNR & ITINERARY PRINTING (HE IED / HE IEP) ---',
            'IBD/P<N>                  : ITINERARY BASIC DISPLAY FOR PASSENGER N',
            'IBP                       : ITINERARY BASIC PRINT FOR ALL PASSENGERS',
            'IED/P<N>                  : ITINERARY EXTENDED DISPLAY FOR PASSENGER N',
            'IEP/P<N>                  : ITINERARY EXTENDED PRINT FOR PASSENGER N',
            'IEP/LP <LANG>/P<N>        : EXTENDED PRINT IN SPECIFIC LANGUAGE (E.G. IEP/LP FR/P2)',
            'IEPJ                      : PRINT SINGLE JOINT ITINERARY FOR ALL PASSENGERS',
            'WRA/RT                    : PRINT ENTIRE ITINERARY SCREEN VIEW',
            'WRA/RH                    : PRINT ENTIRE PNR HISTORY',
        ],
        'SP' => [
            '--- HELP: SPLIT BOOKINGS (HE SP) ---',
            'STEP 1: SP <P1>,<P2>      : SPECIFY PASSENGERS TO SPLIT (E.G. SP 1,4)',
            'STEP 2: RF <NAME>; EF     : FILE NEW PNR FOR SPLIT PASSENGERS',
            'STEP 3: RF <NAME>; ET     : END TRANSACTION IN PARENT PNR',
            'RT AXR                    : RETRIEVE SPLIT PNR RECORDS (LINKED LOCATOR)',
        ],
        'NHP' => [
            '--- HELP: NON-HOMOGENEOUS PNR (HE NHP) ---',
            'HANDLES UNEQUAL SEAT-TO-PASSENGER ALLOCATIONS AUTOMATICALLY AT PNR CREATION.',
            '<SEG>/<SEATS>             : INCREASING SEATS WITH SEGMENT ASSOCIATION (E.G. 3/2)',
            '<SEG>/P<PAX>              : ASSOCIATE SOLD SEGMENT WITH PASSENGER (E.G. 3/P2)',
            '<SEG1>-<SEG2>/P<P1>-<P2>  : ASSOCIATE RANGE OF SEGMENTS TO PASSENGERS',
            'RF <NAME>; ER             : END TRANSACTION AND REDISPLAY PNR',
        ],
        'RRN' => [
            '--- HELP: COPY PNR (HE RRN) ---',
            'RRN                       : COPY ENTIRE ACTIVE PNR',
            'RRN/<PAXCOUNT>            : COPY PNR FOR N PASSENGERS (E.G. RRN/4)',
            'RRN/DP<DAYS>              : COPY PNR FOR N DAYS LATER (E.G. RRN/DP7)',
            'RRN/DM<DAYS>              : COPY PNR FOR N DAYS EARLIER (E.G. RRN/DM3)',
            'RRN/S<S1>,<S2>            : COPY SPECIFIC SEGMENTS ONLY (E.G. RRN/S2,6)',
            'RRN/S<SEG>D<DATE>         : COPY PNR WITH DIFFERENT DATE ON SEGMENT (E.G. RRN/S5D12SEP)',
            'RRN/C<CLS>                : COPY PNR AND CHANGE CLASS (E.G. RRN/CY)',
            'RRA                       : COPY PNR TO CREATE LINKED AXR RECORD',
            'RRP/P<PAX>                : COPY PASSENGER DATA ONLY (E.G. RRP/P2)',
            'RRI                       : COPY ITINERARY SEGMENTS ONLY',
        ],
        'RH' => [
            '--- HELP: PNR HISTORY (HE RH) ---',
            'HE RH                     : LIST OF PNR HISTORY CODES',
            'HERH.148                  : DISPLAY FULL PNR AUDIT TRAIL',
            'RHA                       : PARTIAL HISTORY FOR AIR SEGMENTS ONLY',
            'RHG                       : HISTORY OF GENERAL FACTS (SSR, OSI)',
            'RHS<SEG>                  : RETRIEVE SEGMENT HISTORY (E.G. RHS1)',
            'RHQ                       : DISPLAY QUEUE HISTORY',
            'RH/ALL                    : DISPLAY PNR HISTORY WITH QUEUE HISTORY',
        ],
        'RPD' => [
            '--- HELP: PNR RECALL ENTRIES (HE RPD) ---',
            'RPD/RLC-<RLOC>/<DATE>     : RECALL PURGED PNR BY AMADEUS LOCATOR',
            'RPD/<AIR><FLT>/<DATE>     : RECALL PURGED PNR BY FLIGHT/DATE SEGMENT',
            'EXAMPLE: RPD/RLC-A123B4/03JUL09 | RPD/AF7708/16JUL09',
        ],
        'LP' => [
            '--- HELP: MULTI-LIST SEARCH BY FLIGHT (HE LP) ---',
            'LP/<AIR><FLT>/<DATE>/<PAIR> : MULTI-LIST PNR RETRIEVAL (E.G. LP/TG316/12FEB/DELBKK)',
            'LP/W/<AIR><FLT>/<DATE>/<PAIR>: LIST FOR WAITLISTED SEGMENTS',
            'LP/R/<AIR><FLT>/<DATE>/<PAIR>: LIST FOR RECONFIRMED SEGMENTS',
            'LP/S(<SSR>)/<FLT>/<DATE>/<PAIR>: LIST FOR PASSENGERS WITH SPECIFIC SSR (E.G. FQTV)',
            'LP<LINE>                  : RETRIEVE PNR AT LINE NUMBER FROM LIST (E.G. LP4)',
            'LP0                       : RETURN TO LIST DISPLAY',
        ],
        'LPO' => [
            '--- HELP: SEARCH BY OFFICE (HE LPO) ---',
            'HE LPO / HELPO.358        : OFFICE SEARCH CRITERIA CODES',
            'LPO/A(<AIRLINES>)B(<CITY>): SEARCH BY AIRLINES AND DESTINATION (QUEUE PLACEMENT)',
            'LPO/N(HTL) / LPO/N(CCR)   : SEARCH FOR HOTEL OR CAR SEGMENTS',
            'LPS/PS                    : DISPLAY PASSENGER LIST STATUS TABLE',
            'LPS/D3                    : DISPLAY SEARCH RESULTS FOR LINE 3',
            'LPS<LINE>                 : RETRIEVE PNR FROM LIST (E.G. LPS23)',
            'LPS0                      : RETURN TO LIST DISPLAY',
        ],
        'PROFILES' => [
            '--- HELP: CUSTOMER PROFILE SYSTEM (HE PROFILES) ---',
            'PM                        : ENTER PROFILE MODE',
            'PME                       : EXIT PROFILE MODE',
            'PEE                       : IGNORE & EXIT PROFILE MODE',
            'NM1<LAST>/<FIRST> <TITLE> : CREATE TRAVELLER PROFILE (IN PROFILE MODE)',
            'PCN/<COMPANYNAME>         : CREATE COMPANY PROFILE (IN PROFILE MODE)',
            'PIN/<NICKNAME>            : ADD NICKNAME OR INDEX TO PROFILE',
            'PFO/ <NOTE> / <DATE>      : ADD PROFILE FOLLOW-UP ENTRY',
            'PER                       : SAVE PROFILE',
            'PDN/ <COMPANYNAME>        : DISPLAY COMPANY PROFILE BY NAME',
            'PDN/-<TRAVELLERNAME>      : DISPLAY TRAVELLER PROFILE BY NAME',
            'PD                        : REDISPLAY ACTIVE PROFILE',
            'PI                        : PROFILE IGNORE',
            'PT*                       : CREATE PNR DIRECTLY FROM DISPLAYED PROFILE',
            'PDI/<NICKNAME>            : RETRIEVE PROFILE BY NICKNAME',
            'PC/-1                     : CREATE PROFILE FROM PAX 1 OF ACTIVE PNR',
            'PLT // <COMPANYNAME>      : LIST ALL TRAVELLERS ASSOCIATED TO COMPANY',
        ],
        'FXA' => [
            '--- HELP: BEST PRICER (HE FXA / HE FXB / HE FXR) ---',
            'FXA                       : DISPLAY LIST OF LOWER FARES AVAILABLE',
            'FXB                       : PRICE AND REBOOK TO LOWEST AVAILABLE FARE',
            'FXU<LINE>                 : SELECT FARE FROM FXA LIST, REBOOK (NO TST CREATED)',
            'FXZ<LINE>                 : DISPLAY LOWEST FARE AND ADVISE SEAT AVAILABILITY',
            'FXL                       : PRICE WITHOUT AUTOMATIC REBOOKING FOR STANDARD ACCESS',
        ],
        'HTL' => [
            '--- HELP: HOTEL RESERVATIONS (HE HTL) ---',
            'HL <CITY>                 : HOTEL LIST DISPLAY (E.G. HL LON)',
            'HA <CITY> <DATE>          : HOTEL AVAILABILITY (E.G. HA LON 15DEC)',
            'HS <LINE> / HS <CHAIN>    : HOTEL SELL FROM AVAILABILITY DISPLAY',
            'HF <LINE>                 : HOTEL PROPERTY FEATURES DISPLAY',
            'HP <LINE>                 : HOTEL POLICY AND GUARANTEE INFORMATION',
            'HPR <LINE>                : HOTEL PRICING AND RATE DETAILS',
            'HRC <LINE>                : HOTEL RATE CHANGE INQUIRY',
            'HT <LINE>                 : HOTEL TERMS AND CONDITIONS',
            'HR <LINE>                 : HOTEL POINTS OF REFERENCE (AIRPORT, DOWNTOWN)',
        ],
        'CAR' => [
            '--- HELP: CAR RENTALS (HE CAR) ---',
            'CL <CITY>                 : CAR COMPANY LIST DISPLAY (E.G. CL LAX)',
            'CA <CITY> <DATE>          : CAR AVAILABILITY DISPLAY (E.G. CA LAX 15DEC)',
            'CS <LINE>                 : CAR SELL FROM AVAILABILITY',
            'CR <LINE>                 : CAR RATE FEATURES AND TERMS',
        ],
        'FARES' => [
            '--- HELP: FARES & PRICING (HE FARES) ---',
            'FQD <PAIR> [/D<DATE>] [/A<AL>] : FARE QUOTE DISPLAY (E.G. FQDDOHLHR/D15NOV/AQR)',
            'FQC <CUR1>/<CUR2>         : FARE QUOTE CURRENCY CONVERSION / ROE',
            'FQA <WEIGHT>              : FARE QUOTE EXCESS BAGGAGE',
            'FQN <LINE>                : FARE QUOTE RULES & NOTES',
            'FQR <LINE>                : FARE QUOTE ROUTING',
            'FQP <PAIR>                : FARE QUOTE PRICING',
            'FQM <PAIR>                : FARE QUOTE MILEAGE (MPM / TPM)',
            'FXP                       : PRICE ITINERARY AND STORE TST',
            'FXX                       : PRICE ITINERARY WITHOUT STORING TST',
            'TQT                       : DISPLAY STORED TST BREAKDOWN',
        ],
    ];

    public function handleHelp(string $input, ?string $lastCommand = null): HelpResult
    {
        $clean = strtoupper(trim($input));

        // HE/ or HELP/ -> help on last entered command
        if ($clean === 'HE/' || $clean === 'HELP/') {
            if (! $lastCommand) {
                return new HelpResult([
                    'HE/ - HELP ON LAST TRANSACTION',
                    'NO PREVIOUS TRANSACTION RECORDED IN ACTIVE WORK AREA',
                ]);
            }
            $targetTopic = $this->extractTopicFromCommand($lastCommand);

            return $this->lookupTopic($targetTopic, $lastCommand);
        }

        // HE alone or HELP alone
        if ($clean === 'HE' || $clean === 'HELP' || $clean === '?') {
            return $this->lookupTopic('HE');
        }

        // Topic after HE (e.g. HE SIGN, HE NM, HE MEAL CODES, HE FARE DIAGNOSTIC)
        $topic = trim(preg_replace('/^(?:HELP|HE)\s*/', '', $clean) ?? '');
        if ($topic === '') {
            return $this->lookupTopic('HE');
        }

        return $this->lookupTopic($topic);
    }

    private function lookupTopic(string $rawTopic, ?string $originalCmd = null): HelpResult
    {
        $clean = strtoupper(trim($rawTopic));

        // Exact match
        if (isset(self::HELP_TOPICS[$clean])) {
            return new HelpResult(self::HELP_TOPICS[$clean]);
        }

        // Alias matching
        $aliases = [
            'SIGN-IN' => 'SIGN',
            'SIGNOUT' => 'SIGN',
            'SIGNIN' => 'SIGN',
            'NAME' => 'NM',
            'NAMES' => 'NM',
            'PHONE' => 'AP',
            'CONTACT' => 'AP',
            'TICKET' => 'TK',
            'TICKETING' => 'TK',
            'AVAILABILITY' => 'AN',
            'SCHEDULE' => 'SN',
            'SCHEDULES' => 'SN',
            'SELL' => 'SS',
            'RETRIEVE' => 'RT',
            'CLAIM' => 'RO',
            'CANCEL' => 'XE',
            'SPLIT' => 'SP',
            'COPY' => 'RRN',
            'HISTORY' => 'RH',
            'RECALL' => 'RPD',
            'TIMATIC' => 'TI',
            'PROFILE' => 'PROFILES',
            'HOTEL' => 'HTL',
            'HOTELS' => 'HTL',
            'CARS' => 'CAR',
            'FARE' => 'FARES',
            'PRICING' => 'FARES',
            'BEST PRICER' => 'FXA',
            'FXB' => 'FXA',
            'FXR' => 'FXA',
            'IEP' => 'IED',
            'IBD' => 'IED',
            'IBP' => 'IED',
            'ACR' => 'AC',
            'XI' => 'XE',
            'DL' => 'XE',
            'SB' => 'XE',
            'AM' => 'AB',
            'RC' => 'RM',
            'RIR' => 'RM',
            'OP' => 'RM',
            'OS' => 'OSI',
        ];

        if (isset($aliases[$clean]) && isset(self::HELP_TOPICS[$aliases[$clean]])) {
            return new HelpResult(self::HELP_TOPICS[$aliases[$clean]]);
        }

        // Check if starts with any known topic prefix (e.g. DIR 9W -> DIR, HTLXX -> HTL)
        foreach (self::HELP_TOPICS as $key => $lines) {
            if (str_starts_with($clean, $key)) {
                return new HelpResult($lines);
            }
        }

        // Generic fallback with topic reference
        $lines = [
            "--- AMADEUS HELP: {$clean} ---",
            "SPECIFIC HELP NOT FOUND FOR \"{$clean}\".",
            'TYPE "HE HE" TO VIEW THE AMADEUS TOPIC INDEX.',
            'AVAILABLE TOPICS INCLUDE: SIGN, AIS, NM, AP, TK, AN, SN, SS, RT, RO, XE, SP, TI, FXA, HTL, CAR',
        ];

        return new HelpResult($lines);
    }

    private function extractTopicFromCommand(string $cmd): string
    {
        $c = strtoupper(trim($cmd));
        $two = substr($c, 0, 2);

        $map = [
            'JI' => 'SIGN', 'JO' => 'SIGN', 'JX' => 'SIGN', 'JM' => 'SIGN', 'JD' => 'SIGN', 'JB' => 'SIGN',
            'GG' => 'AIS',
            'NM' => 'NM',
            'AP' => 'AP',
            'TK' => 'TK',
            'AN' => 'AN', 'AA' => 'AN',
            'SN' => 'SN', 'TN' => 'TN',
            'SS' => 'SS', 'SO' => 'SO', 'SI' => 'SI',
            'RT' => 'RT', 'RL' => 'RT',
            'RO' => 'RO',
            'XE' => 'XE', 'XI' => 'XE', 'DL' => 'XE', 'SB' => 'XE',
            'SP' => 'SP',
            'RRN' => 'RRN', 'RRA' => 'RRN',
            'RH' => 'RH',
            'RP' => 'RPD',
            'LP' => 'LP',
            'PM' => 'PROFILES', 'PD' => 'PROFILES', 'PC' => 'PROFILES',
            'FX' => 'FXA',
            'HL' => 'HTL', 'HA' => 'HTL', 'HS' => 'HTL',
            'CL' => 'CAR', 'CA' => 'CAR', 'CS' => 'CAR',
            'TI' => 'TI',
            'DD' => 'DD', 'DF' => 'DD',
            'FQ' => 'FARES',
        ];

        return $map[$two] ?? $two;
    }
}
