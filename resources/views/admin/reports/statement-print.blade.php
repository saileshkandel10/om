<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Official Shareholder & Reward Statement - OM Connect</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 20px; font-size: 14px; background: #fff; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); border-radius: 12px; }
        .header-table { width: 100%; border-bottom: 2px solid #CD0B6F; padding-bottom: 15px; margin-bottom: 20px; }
        .header-table td { vertical-align: middle; }
        .title { font-size: 22px; font-weight: bold; color: #CD0B6F; margin: 0; }
        .tagline { font-size: 12px; font-style: italic; color: #555; margin-top: 4px; }
        .info-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .info-table td { padding: 6px 0; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 13px; }
        .table th { background-color: #f8f9fa; color: #1E3A5F; font-weight: bold; }
        .badge { display: inline-block; padding: 4px 8px; font-size: 11px; font-weight: bold; border-radius: 4px; background: #e2e8f0; color: #333; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 15px; }
        .print-btn { display: block; width: 100%; max-width: 200px; margin: 20px auto; padding: 10px; background: #CD0B6F; color: #fff; text-align: center; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; }
        @media print { .print-btn { display: none; } body { padding: 0; } .invoice-box { border: none; box-shadow: none; padding: 0; } }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td>
                    <h2 class="title">OM CONNECT</h2>
                    <div class="tagline">Om Megashree Pharmaceuticals Limited</div>
                    <div style="font-size: 11px; color: #666; margin-top: 2px;">"स्वास्थ्य र राष्ट्रका लागि एक सङ्कल्प"</div>
                </td>
                <td style="text-align: right;">
                    <strong>Official Statement</strong><br>
                    Date: {{ now()->format('M d, Y') }}<br>
                    Ref: STMT-{{ $user->id }}-{{ date('Ymd') }}
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <strong>Shareholder / Patient Details:</strong><br>
                    Name: <strong>{{ $user->name }}</strong><br>
                    Email: {{ $user->email }}<br>
                    Mobile: {{ $user->mobile ?? 'N/A' }}
                </td>
                <td style="width: 50%;">
                    <strong>Account & BOID Status:</strong><br>
                    BOID: <strong>{{ $account?->boid ?? $master?->boid ?? 'Not Linked' }}</strong><br>
                    Shares Owned: {{ $account?->sharesOwned ?? $master?->units ?? '0' }} units<br>
                    Status: <span class="badge {{ ($account?->isVerified ?? false) ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($account?->verificationStatus ?? 'Unverified') }}</span>
                </td>
            </tr>
        </table>

        <h3>Reward Wallet Summary</h3>
        <table class="table">
            <tr>
                <th>Total Loyalty Points</th>
                <th>Bonus Credits</th>
                <th>Shareholder Royalty</th>
                <th>Total Wallet Value</th>
            </tr>
            <tr>
                <td><strong>{{ number_format($wallet?->totalPoints ?? 0, 2) }}</strong></td>
                <td>Rs {{ number_format($wallet?->totalBonus ?? 0, 2) }}</td>
                <td>Rs {{ number_format($wallet?->totalRoyalty ?? 0, 2) }}</td>
                <td><strong style="color: #CD0B6F;">Rs {{ number_format(($wallet?->totalPoints ?? 0) + ($wallet?->totalBonus ?? 0) + ($wallet?->totalRoyalty ?? 0), 2) }}</strong></td>
            </tr>
        </table>

        <h3>Purchase Bills & OCR History</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Points Awarded</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                    <tr>
                        <td>{{ $bill->invoice_number ?? 'INV-'.$bill->id }}</td>
                        <td>{{ $bill->created_at->format('M d, Y') }}</td>
                        <td>Rs {{ number_format($bill->total_amount, 2) }}</td>
                        <td>+{{ number_format($bill->points_awarded, 2) }} pts</td>
                        <td>
                            <span class="badge {{ $bill->validation_status === 'approved' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($bill->validation_status ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #777;">No purchase bills recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 50px; display: flex; justify-content: space-between;">
            <div style="text-align: center; width: 200px; border-top: 1px solid #333; padding-top: 5px; float: left;">
                Prepared By
            </div>
            <div style="text-align: center; width: 200px; border-top: 1px solid #333; padding-top: 5px; float: right;">
                Authorized Signature
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Om Megashree Pharmaceuticals Limited. All rights reserved. • Generated via OM Connect Platform.
        </div>

        <button onclick="window.print()" class="print-btn">Print / Download PDF</button>
    </div>
</body>
</html>
