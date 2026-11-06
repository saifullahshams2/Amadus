# ✈️ Amadeus Basic Reservation Guide

## Sign-In & Sign-Out
*   **Sign-in first available area:** `JI0001AA/SU`
*   **Sign-in All areas:** `JI*0001AA/SU`
*   **Sign-in with password together:** `JI0001AA/SU-Password`
*   **Sign-out from first available area:** `JO`
*   **Sign-in out from all areas:** `JO*`

## Availability Display
*   **Available class to book or waitlist:** `AN01MARDOHDXB`
*   **Availability for Specified time:** `AN01MARDOHDXB1200`
*   **Availability for specified Airline:** `AN01MARDOHDXB/AEK`
*   **Availability for specified via connection:** `AN01MARDOHDXB/XAUH`
*   **Availability for specified Class:** `AN01MARDOHDXB/CV`
*   **Combination:** `AN01MARDOHDXB1200/XAUH/AGF/CM`
*   **Availability for In-bound and Out-bound:** `AN12MARDOHDXB*15MAR`
*   **Availability Display for Close or Book able:** `SN01MARDOHDXB`

## Name Elements - NM
*   **Single Adult Passenger Name:** `NM1HUSSAIN/ALI MR`
*   **Same family Name for 2 Adults:** `NM2HUSSAIN/ALI MR/M MRS`
*   **More than on Name enter together:** `NM1HUSSAIN/A`
    *   `MR;NM1SYED/ALI MR`
*   **Child passenger:** `NM1HUSSAIN/NASEER MSTR(CHD)`
*   **Infant Same family Name as Adult:** `NM1HUSSAIN/M MRS(INF/NABIL)`
*   **Infant with Different family name:** `NM1HUSSAIN/M MRS(INFALEMADI/ALI)`
*   **Remove Child Title from Passenger 2:** `2/` (Type Enter)
*   **Add Child Title to passenger 3:** `3/(CHD)` (Type Enter)
*   **Remove Infant from passenger 4:** `4/` (Type Enter)
*   **Add Infant to passenger 5:** `5/(INF/ALI)`
*   **Update 2nd Name of passenger 6:** `6/MARIYUM MRS`

### Segment Sell - SS
*   **Sell 2 seats in V-cls on line 4 of Availability:** `SS2V4`
*   **Sell 2 seats in J-cls leg 1 and F-cls leg 2 of line 3:** `SS2JF3`
*   **Long sell:** `SSQR001B15MARDOHLHRNN2`
*   **Sell In-bound and Out-bound Display:** `SS2V1*V13`
*   **Dead Segment as OK Status for Issue Tickets:**
    *   `SSQR001B15MARDOHLHRGK2/QR`
*   **Dead Segment as WL Status for Issue Tickets:**
    *   `SSQR001B15MARDOHLHRGL2/QR`
*   **Open Segment for Ticket Issuance:** `SO QR B LHRDOH`
*   **Surface Segment:** `SIARNK`

## Phone Field - AP
*   **Phone Number Field:** `AP 4667579`
*   **Phone Number Field for specific Location:** `AP DOH 4667579`
*   **Enter your office Number:** `AP` (Type Enter)
*   **E-mal Address:** `APE-abc@abc.com`

## Ticket Arrangement – TK
*   **No Time limit:** `TKOK`
*   **Specific Time Limit with Date and Time:** `TKTL12MAR/1200`
*   **Auto Cancellation on Specific Date and Time:** `TKXL12MAR/1200`

## Received From Field - RF
*   **Modification asked by Passenger:** `RF PAX` or `RF P`
*   **Modification by Agent:** `RF ALI`
*   **Modification by Mr. Al-Emadi:** `RF MR ALEMADI`

## End Transaction – ET / ER
*   **End the File and close the PNR:** `ET`
*   **End the File and Redisplay PNR:** `ER`

## Retrieve PNR - RT
*   **Retrieve PNR Number:** `RTXXXXXX`
*   **Retrieve By Name:** `RT/AHMED`
*   **Retrieve By Alphabet:** `RT/A`
*   **Retrieve PNR on line 4 of the List:** `RT4`
*   **Retrieve Other Airline PNR:** `RL`

## Display PNR List Booked Specific office – LM
*   **Specific flight Number and Date:** `LM/QR001/12MAR/DOHLHR`
*   **Retrieve or Display PNR ON Line 5 of the List:** `LM5`

## Frequent Flyer Number - FFN
*   **Enter Frequent Flyer Number Automated:** `FFNQR-1111111111`
*   **Enter Frequent Flyer Number Automated for specific PAX2:** `FFNQR-1111111111/P2`
*   **Manual Entry for Frequent Flyer:** `SRFQTVEK-EK111111111`
*   **Manual Entry for Frequent Flyer for PAX 3:** `SRFQTVEK-EK111111111/P3`

## Seat Selection - ST (General)
*   **Display Seat Map for Segment Number 3:** `SM3`
*   **Request specific seat Number:** `ST/R/20K`
*   **Request specific seat Number for PAX 4:** `ST/R/20K/P4`
*   **Request specific seat for specific segment 4:** `ST/R/20K/S4`
*   **Generic Smoking Seat:** `ST/S`
*   **Generic Non-Smoking:** `ST`

## Seat Selection - ST (Specific)
*   **Generic Non-Smoking Window for Passenger #2:** `ST/W/P2`
*   **Generic Non-Smoking Aisle for Segment # 4:** `ST/A/S4`
*   **Generic Non-Smoking Bulk Head for Segment # 5 and PAX # 3:** `ST/B/S5/P3`

## Other Services Information - OS
*   **Information for specific carrier:** `OS EK PAX IS VIP`
*   **Information for All carriers:** `OS YY PAX IS VIP`

## Services Required - SR
*   **Specific Meal request for All Carriers and Segments:** `SR VGML`
*   **Specific Meal request for Specific carrier and Passenger:** `SR VGML LH/P2`
*   **Specific Meal request for Specific carrier and Segment:** `SR VGML LH/S4`
*   **Passport Information (Example 1):** `SR PSPT EKHK1-A123456-PK-04FEB67-MINHAS/ZAFARMR-M-H/P1`
*   **Passport Information (Example 2):** `SR PSPT EKHK1-A123456-PK-04FEB67-MINHAS/AMRS-F-H/P2`
*   **Passport Information (Example 3):** `SR PSPT EKHK1-A123456-PK-04FEB67-MINHAS/TALAL-MI/P2`
*   **Passport Information (Example 4):** `SR PSPT EKHK1-A123456-PK-04FEB67-MINHAS/HALA-FI/P2`

## Cancellation – XE / XI
*   **Specific Segment cancel:** `XE4`
*   **Cancel Itinerary:** `XI`

## Change Booking Class – SB
*   **Change booking class from B-cls to J-cls All segments:** `SBJ`
*   **Change booking class from B-cls to J-cls Specific segment # 4:** `SBJ4`
*   **Change booking date for Specific segment # 3:** `SB30MAR3`

## Retrieve History - RH
*   **Retrieve History (General):** `RH`
*   **Retrieve History for specific Segment # 4:** `RHS4`
*   **Retrieve History for other Airline PNR:** `RLH`

## Remarks – RM / RC
*   **General Remark only View by Amadeus Airline Users:** `RM please confirm segment 4`
*   **Confidential Remarks View by Your office:** `RC charge extra QAR400.00`

## Print – WRS / WRA
*   **Print screen only on default printer:** `WRS/RT`
*   **Print All on default printer:** `WRA/RT`

## Option – OP
*   **To remind you in Queue # 3 for Action:** `OP12MAR/free Text`

## Queue – QT / QS / QE / QI / QN / QD
*   **Display Queue Bank:** `QT`
*   **Display specific Queue 2, Category (C)30, and Date Range(D) 1:** `QS2C30D1`
*   **Ignore Queue and PNR still in Queue:** `QI`
*   **Remove from Queue:** `QN`
*   **Delay PNR in Queue and Retrieve next Queue:** `QD`
*   **To place Queue on Queue 30:** `QE30`

## Step for Split PNR – SP
*   **Split passenger # 3:** `SP3`
*   **Received from (for split):** `RF P`
*   **File Split PNR:** `EF`
*   **Received Again:** `RF P`
*   **End Transaction:** `ET` or `ER`

## Manual Ticket Number – FH
*   **Basic Entry for Automated Issued Ticket:** `FHA 157-9196789012`
*   **Basic Entry for Manual Issued Ticket:** `FHM 157-2124564781`
*   **Basic Entry for Electronic Issued Ticket:** `FHE 157-9196789012`
*   **Automated Issued Ticket For Infant:** `FHA INF 157-9196789012`
*   **Automated Issued Ticket For Non Infant:** `FHA PAX 157-9196789012`
*   **Automated Issued Ticket For Specific Passenger:** `FHA PAX 157-9196789012/P2`
*   **Automated Issued Ticket as Conjunction:** `FHA PAX 157-9196789012-013`

### Fareast Domestic Airlines Specific Field (TKNA)
*   **Ticket Numbers in TKNA field:** `SROTHS GA- TKNA 126-4401123456`
