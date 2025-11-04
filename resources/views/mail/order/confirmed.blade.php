@component('mail::message')
@php($orderUrl = rtrim(config('app.url'), '/').'/orders/'.$order->id)

<div style="background:linear-gradient(135deg,#f0f7ff,#fff);padding:24px;border-radius:12px;border:1px solid #e6efff;">
  <h1 style="margin:0 0 8px;font-size:22px;line-height:1.3;color:#0f172a;">Order Confirmed 🎉</h1>
  <p style="margin:0 0 16px;color:#334155;font-size:14px;">Hello <strong>{{ $user->name }}</strong>, your order has been confirmed.</p>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
    <tr>
      <td style="padding:14px;border-bottom:1px solid #e2e8f0;background:#f8fafc;width:35%;color:#475569;font-size:12px;">Order ID</td>
      <td style="padding:14px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">#{{ $order->id }}</td>
    </tr>
    <tr>
      <td style="padding:14px;border-bottom:1px solid #e2e8f0;background:#f8fafc;width:35%;color:#475569;font-size:12px;">Status</td>
      <td style="padding:14px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#065f46;"><strong>{{ ucfirst($order->status) }}</strong></td>
    </tr>
    <tr>
      <td style="padding:14px;border-bottom:1px solid #e2e8f0;background:#f8fafc;width:35%;color:#475569;font-size:12px;">Total</td>
      <td style="padding:14px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;"><strong>{{ $order->total_amount }}</strong></td>
    </tr>
    <tr>
      <td style="padding:14px;background:#f8fafc;width:35%;color:#475569;font-size:12px;">Date</td>
      <td style="padding:14px;font-size:14px;color:#0f172a;">{{ optional($order->created_at)->format('M d, Y H:i') }}</td>
    </tr>
  </table>

  @if($order->relationLoaded('items') || $order->items()->exists())
    <h3 style="font-size:16px;margin:18px 0 8px;color:#0f172a;">Items</h3>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
      <thead>
        <tr style="background:#f8fafc;">
          <th align="left" style="padding:12px;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;font-size:12px;">Product</th>
          <th align="center" style="padding:12px;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;font-size:12px;">Qty</th>
          <th align="right" style="padding:12px;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;font-size:12px;">Price</th>
          <th align="right" style="padding:12px;color:#475569;font-weight:600;border-bottom:1px solid #e2e8f0;font-size:12px;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach(($order->relationLoaded('items') ? $order->items : $order->items()->with('product')->get()) as $item)
          <tr>
            <td style="padding:12px;border-bottom:1px solid #f1f5f9;color:#0f172a;font-size:13px;">{{ optional($item->product)->name ?? ('#'.$item->product_id) }}</td>
            <td align="center" style="padding:12px;border-bottom:1px solid #f1f5f9;color:#0f172a;font-size:13px;">{{ $item->quantity }}</td>
            <td align="right" style="padding:12px;border-bottom:1px solid #f1f5f9;color:#0f172a;font-size:13px;">{{ $item->price }}</td>
            <td align="right" style="padding:12px;border-bottom:1px solid #f1f5f9;color:#0f172a;font-size:13px;">{{ $item->subtotal }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  @component('mail::button', ['url' => $orderUrl])
    View Order
  @endcomponent

  <p style="margin:12px 0 0;color:#64748b;font-size:12px;">If you didn’t make this purchase, please contact our support team immediately.</p>
</div>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
