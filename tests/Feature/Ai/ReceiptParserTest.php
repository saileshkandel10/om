<?php

use App\Ai\Agents\ReceiptParser;

test('receipt parser extracts exact bill data accurately', function () {
    $rawOcrText = <<<'TEXT'
    SHREE OM PHARMACY & SURGICALS
    Tripureshwor, Kathmandu, Nepal
    PAN: 301234567 | Tel: 01-4234567
    Tax Invoice
    Invoice No: INV-2024-8821
    Date: 2024-09-15
    Dr. Name: Dr. S. K. Sharma (NMC: 5542)
    Patient: Ram Bahadur

    S.N. Particulars Qty Rate Amount
    1. Paracetamol 500mg Tab 20 2.50 50.00
    2. Pantoprazole 40mg 10 12.00 120.00
    3. Amoxicillin 500mg Cap 15 10.00 150.00
    4. Cetirizine 10mg 10 4.00 40.00

    Sub Total: 360.00
    Discount: 0.00
    VAT (13%): 46.80
    Grand Total: 406.80
    Thank you! Visit Again!
    TEXT;

    $parser = new ReceiptParser;
    $result = $parser->parse($rawOcrText);

    expect($result['pharmacy_name'])->toBe('SHREE OM PHARMACY & SURGICALS')
        ->and($result['invoice_number'])->toBe('INV-2024-8821')
        ->and($result['invoice_date'])->toBe('2024-09-15')
        ->and($result['total_amount'])->toBe(406.80)
        ->and($result['subtotal_amount'])->toBe(360.00)
        ->and($result['tax_amount'])->toBe(46.80)
        ->and($result['doctor_name'])->toBe('Dr. S. K. Sharma')
        ->and($result['doctor_nmc'])->toBe('5542')
        ->and($result['city'])->toBe('Kathmandu')
        ->and($result['items'])->toHaveCount(4);

    expect($result['items'][0]['name'])->toBe('Paracetamol 500mg Tab')
        ->and($result['items'][0]['quantity'])->toBe(20.0)
        ->and($result['items'][0]['unit_price'])->toBe(2.50)
        ->and($result['items'][0]['amount'])->toBe(50.00);

    expect($result['items'][1]['name'])->toBe('Pantoprazole 40mg')
        ->and($result['items'][1]['quantity'])->toBe(10.0)
        ->and($result['items'][1]['unit_price'])->toBe(12.00)
        ->and($result['items'][1]['amount'])->toBe(120.00);

    expect($result['items'][2]['name'])->toBe('Amoxicillin 500mg Cap')
        ->and($result['items'][2]['quantity'])->toBe(15.0)
        ->and($result['items'][2]['unit_price'])->toBe(10.00)
        ->and($result['items'][2]['amount'])->toBe(150.00);

    expect($result['items'][3]['name'])->toBe('Cetirizine 10mg')
        ->and($result['items'][3]['quantity'])->toBe(10.0)
        ->and($result['items'][3]['unit_price'])->toBe(4.00)
        ->and($result['items'][3]['amount'])->toBe(40.00);
});

test('receipt parser extracts kanti bal health care dispensary bill', function () {
    $rawOcrText = <<<'TEXT'
    Plaintext
    KANTI BAL HEALTH CARE DISPENSARY
    Maharajgunj, Kathmandu
    PAN: 302918471 | DDA Reg: 1109/080
    Bill No: KBD-83-04918	Date: 2083-06-12 (02:15 PM)
    Patient: Baby Aarav Pokharel (Age: 3 yrs)	Doctor: Dr. R. Karki (Pediatric)
    Parent : Dipak Pokharel	Mode: Cash
    SN Item Description	Batch	Exp	Qty	Unit Price	Total
    45.00	45.00
    1.	Niko Paediatric Syrup 60ml	NK-109	10/27
    2. Cetriz Drop 15ml (Lomus)	CZ-028	03/28	60.00	60.00	75.00
    3. Nasivion Paediatric Drops	NS-552	08/27	1	75.00	75.00
    4. Electrobion ORS Powder	EB-901	11/27	25.00
    Rs-	255.00
    Gross Amount:	0.00
    R$-
    Discount:
    Rs. 255.00
    Net Payable:
    Received Cash: Rs, 500,00 | Change: R$. 245,00
    Note: Give syrup strictly using the provided measuring cup.
    TEXT;

    $parser = new ReceiptParser;
    $result = $parser->parse($rawOcrText);

    expect($result['pharmacy_name'])->toBe('KANTI BAL HEALTH CARE DISPENSARY')
        ->and($result['pan_number'])->toBe('302918471')
        ->and($result['dda_reg'])->toBe('1109/080')
        ->and($result['invoice_number'])->toBe('KBD-83-04918')
        ->and($result['invoice_date'])->toBe('2083-06-12')
        ->and($result['invoice_time'])->toBe('02:15 PM')
        ->and($result['customer_name'])->toBe('Baby Aarav Pokharel')
        ->and($result['doctor_name'])->toBe('Dr. R. Karki')
        ->and($result['payment_mode'])->toBe('Cash')
        ->and($result['total_amount'])->toBe(255.00)
        ->and($result['items'])->toHaveCount(4);

    expect($result['items'][0]['name'])->toBe('Niko Paediatric Syrup 60ml')
        ->and($result['items'][0]['batch_no'])->toBe('NK-109')
        ->and($result['items'][0]['expiry_date'])->toBe('10/27')
        ->and($result['items'][0]['unit_price'])->toBe(45.00)
        ->and($result['items'][0]['amount'])->toBe(45.00);

    expect($result['items'][1]['name'])->toBe('Cetriz Drop 15ml (Lomus)')
        ->and($result['items'][1]['batch_no'])->toBe('CZ-028')
        ->and($result['items'][1]['unit_price'])->toBe(60.00)
        ->and($result['items'][1]['amount'])->toBe(60.00);

    expect($result['items'][2]['name'])->toBe('Nasivion Paediatric Drops')
        ->and($result['items'][2]['batch_no'])->toBe('NS-552')
        ->and($result['items'][2]['unit_price'])->toBe(75.00)
        ->and($result['items'][2]['amount'])->toBe(75.00);

    expect($result['items'][3]['name'])->toBe('Electrobion ORS Powder')
        ->and($result['items'][3]['batch_no'])->toBe('EB-901')
        ->and($result['items'][3]['unit_price'])->toBe(25.00)
        ->and($result['items'][3]['amount'])->toBe(25.00);
});

test('receipt parser extracts bill 28 with 4 items without false positives', function () {
    $rawOcrText = <<<'TEXT'
    Plaintext
    GRANDE CITY PHARMACY & WELLNESS
    Kantipath, Kathmandu
    PAN: 601839201 | DDA Reg: 4402/081
    Bill No: GCP-83-10492	Date: 2083-06-12 (09:30 AM)
    Patient: Mohan Lal Shrestha (Age: 58/M)	Doctor: Dr. S. K. Jha (NMC: 5120)
    SN Item Description	Batch	Exp Qty	Unit Price Total
    1. Metfor-SR 500mg (National)	MF-441	12/28	60	4.50	270.00
    2. Amlopress 5mg (Asian Pharma) AM-812	09/27	30	4.20	126.00
    3. Atorva 10mg (NPL)	AT-303	04/28	30	11.00	330.00
    4. Pantop 40mg (Asian Pharma)	PT-119	01/28	30	8.50	255.00
    Gross Amount:	Rs.	981.00
    Senior Citizen Concession (5%):	-RS -	49.05
    Net Payable (VAT Exempt):	Rs -	931.95
    Rounded Total:	Rs. 932.00
    Payment Method: Fonepay (QR)
    Pharmacist: Anita Shakya (NPC: 5410)
    TEXT;

    $parser = new ReceiptParser;
    $result = $parser->parse($rawOcrText);

    expect($result['pharmacy_name'])->toBe('GRANDE CITY PHARMACY & WELLNESS')
        ->and($result['invoice_number'])->toBe('GCP-83-10492')
        ->and($result['invoice_date'])->toBe('2083-06-12')
        ->and($result['invoice_time'])->toBe('09:30 AM')
        ->and($result['customer_name'])->toBe('Mohan Lal Shrestha')
        ->and($result['doctor_name'])->toBe('Dr. S. K. Jha')
        ->and($result['doctor_nmc'])->toBe('5120')
        ->and($result['payment_mode'])->toBe('Fonepay (QR)')
        ->and($result['subtotal_amount'])->toBe(981.00)
        ->and($result['discount_amount'])->toBe(49.05)
        ->and($result['total_amount'])->toBe(931.95)
        ->and($result['items'])->toHaveCount(4);

    expect($result['items'][0]['name'])->toBe('Metfor-SR 500mg (National)')
        ->and($result['items'][0]['batch_no'])->toBe('MF-441')
        ->and($result['items'][0]['expiry_date'])->toBe('12/28')
        ->and($result['items'][0]['quantity'])->toBe(60.0)
        ->and($result['items'][0]['unit_price'])->toBe(4.50)
        ->and($result['items'][0]['amount'])->toBe(270.00);

    expect($result['items'][1]['name'])->toBe('Amlopress 5mg (Asian Pharma)')
        ->and($result['items'][1]['batch_no'])->toBe('AM-812')
        ->and($result['items'][1]['quantity'])->toBe(30.0)
        ->and($result['items'][1]['unit_price'])->toBe(4.20)
        ->and($result['items'][1]['amount'])->toBe(126.00);

    expect($result['items'][2]['name'])->toBe('Atorva 10mg (NPL)')
        ->and($result['items'][2]['batch_no'])->toBe('AT-303')
        ->and($result['items'][2]['quantity'])->toBe(30.0)
        ->and($result['items'][2]['unit_price'])->toBe(11.00)
        ->and($result['items'][2]['amount'])->toBe(330.00);

    expect($result['items'][3]['name'])->toBe('Pantop 40mg (Asian Pharma)')
        ->and($result['items'][3]['batch_no'])->toBe('PT-119')
        ->and($result['items'][3]['quantity'])->toBe(30.0)
        ->and($result['items'][3]['unit_price'])->toBe(8.50)
        ->and($result['items'][3]['amount'])->toBe(255.00);
});

test('receipt parser extracts distributor invoice plaintext format', function () {
    $rawOcrText = "Plaintext\t\n"
        ."M/S HIMALAYAN PHARMA DISTRIBUTORS\tINVOICE NO: HPD/GST/83-84/0488\t\n"
        ."Chhetrapati, Kathmandu\tDATE\t: 2083/06/12\t\n"
        ."PAN: 301994820 | DDA: W-4019\tPAYMENT\t: CREDIT 15 DAYS\t\n"
        ."BUYER: Shrestha Medical Hall, Bhaktapur\tBUYER PAN : 605581902\t\n"
        ."SN\tITEM NAME\tMFR\tBATCH\tEXP\tMRP\tW. RATE\tQTY\tFREE\tAMOUNT\t\n"
        ."01\tNiko 500mg Strip\tNPL\tNK-402\t11/27\t20.00\t15.20\t100\t10 1520.00\t\n"
        ."02\tCef-3 200mg Strip\tDJPL CF-104 05/27 280.00\t215.00\t20\t฿ 4300.00\t\n"
        ."03 Jeevan Jal Sachet\tNAT\tJJ-110 04/27 10.00\t7.80\t200\t20 1560.00\t\n"
        ."04 Cetriz 10mg Strip\tLONUS\tC2-551 01/28 40.00\t30.50\t50\t5 1525.00\t\n"
        ."TOTAL QUANTITY: 370 + 35 FREE\tSUBTOTAL :\t8905.00\t\n"
        ."SPECIAL TRADE DISCOUNT (3.5%)\t: DISCOUNT :\t-311.67\t\n"
        ."TAXABLE BASE\t: TAXABLE\t8593.33\t\n"
        ."VALUE ADDED TAX (0% EXEMPT)\t: VAT\t0.00\t\n"
        ."NET PAYABLE (NPR)\t: TOTAL\t: 8593.33";

    $parser = new ReceiptParser;
    $result = $parser->parse($rawOcrText);

    expect($result['pharmacy_name'])->toBe('HIMALAYAN PHARMA DISTRIBUTORS')
        ->and($result['invoice_number'])->toBe('HPD/GST/83-84/0488')
        ->and($result['pan_number'])->toBe('301994820')
        ->and($result['dda_reg'])->toBe('W-4019')
        ->and($result['customer_name'])->toBe('Shrestha Medical Hall, Bhaktapur')
        ->and($result['payment_mode'])->toBe('CREDIT 15 DAYS')
        ->and($result['total_amount'])->toBe(8593.33)
        ->and($result['discount_amount'])->toBe(311.67)
        ->and($result['items'])->toHaveCount(4);

    expect($result['items'][0]['name'])->toBe('Niko 500mg Strip')
        ->and($result['items'][0]['quantity'])->toBe(100.0)
        ->and($result['items'][0]['unit_price'])->toBe(15.20)
        ->and($result['items'][0]['amount'])->toBe(1520.00)
        ->and($result['items'][0]['batch_no'])->toBe('NK-402')
        ->and($result['items'][0]['expiry_date'])->toBe('11/27');

    expect($result['items'][1]['name'])->toBe('Cef-3 200mg Strip')
        ->and($result['items'][1]['quantity'])->toBe(20.0)
        ->and($result['items'][1]['unit_price'])->toBe(215.00)
        ->and($result['items'][1]['amount'])->toBe(4300.00);

    expect($result['items'][2]['name'])->toBe('Jeevan Jal Sachet')
        ->and($result['items'][2]['quantity'])->toBe(200.0)
        ->and($result['items'][2]['unit_price'])->toBe(7.80)
        ->and($result['items'][2]['amount'])->toBe(1560.00);

    expect($result['items'][3]['name'])->toBe('Cetriz 10mg Strip')
        ->and($result['items'][3]['quantity'])->toBe(50.0)
        ->and($result['items'][3]['unit_price'])->toBe(30.50)
        ->and($result['items'][3]['amount'])->toBe(1525.00);
});

test('receipt parser extracts tabular items with multiplication symbols', function () {
    $rawOcrText = <<<'TEXT'
    Apollo Medico Store
    Biratnagar, Nepal
    Bill No: 9482
    Date: 12/04/2026

    Azithromycin 500mg 3 x 45.00 135.00
    Cough Syrup 100ml 1 x 110.00 110.00
    Vitamin C 500mg 30 x 3.00 90.00

    Total Amount: Rs. 335.00
    TEXT;

    $parser = new ReceiptParser;
    $result = $parser->parse($rawOcrText);

    expect($result['pharmacy_name'])->toBe('Apollo Medico Store')
        ->and($result['invoice_number'])->toBe('9482')
        ->and($result['total_amount'])->toBe(335.00)
        ->and($result['city'])->toBe('Biratnagar')
        ->and($result['items'])->toHaveCount(3);

    expect($result['items'][0]['name'])->toBe('Azithromycin 500mg')
        ->and($result['items'][0]['quantity'])->toBe(3.0)
        ->and($result['items'][0]['unit_price'])->toBe(45.00)
        ->and($result['items'][0]['amount'])->toBe(135.00);
});

test('receipt parser handles blank or illegible text safely without inventing data', function () {
    $parser = new ReceiptParser;
    $result = $parser->parse('');

    expect($result['pharmacy_name'])->toBeNull()
        ->and($result['invoice_number'])->toBeNull()
        ->and($result['invoice_date'])->toBeNull()
        ->and($result['total_amount'])->toBeNull()
        ->and($result['doctor_name'])->toBeNull()
        ->and($result['items'])->toBeEmpty();
});
