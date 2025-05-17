@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Receipt — {{ $order->reference_no }}</h5>
          <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Back</a>
        </div>
        <div class="card-body">
          <div id="printable-receipt-{{ $order->id }}" class="receipt-container mx-auto">
            <div class="receipt-header">
              <img src="{{ asset('img/logo.png') }}" alt="Logo" class="receipt-logo">
              <h1>LAUNDRY RECEIPT</h1>
            </div>
            <div class="receipt-content">
              <div class="receipt-row">
                <span>Reference #:</span>
                <span>{{ $order->reference_no }}</span>
              </div>
              <div class="receipt-row">
                <span>Customer:</span>
                <span>{{ $order->user->name ?? 'N/A' }}</span>
              </div>
              <div class="receipt-row">
                <span>Date:</span>
                <span>{{ $order->created_at->format('M d, Y h:i A') }}</span>
              </div>
              <div class="receipt-row">
                <span>Status:</span>
                <span>{{ ucfirst($order->status) }}</span>
              </div>
              <div class="receipt-row">
                <span>Service:</span>
                <span>{{ $order->service }}</span>
              </div>
              <div class="receipt-row">
                <span>Operator:</span>
                <span>
                  @if($order->operator_type === 'admin')
                    {{ \App\Models\User::find($order->operator_id)?->name ?? 'N/A' }} (Admin)
                  @elseif($order->operator_type === 'staff')
                    {{ \App\Models\Employee::find($order->operator_id)?->name ?? 'N/A' }} (Staff)
                  @else
                    N/A
                  @endif
                </span>
              </div>
              <div class="receipt-row">
                <span>Weight:</span>
                <span>{{ $order->weight }} kg</span>
              </div>
              <div class="receipt-row total">
                <span>Total:</span>
                <span>₱{{ number_format($order->total_price, 2) }}</span>
              </div>

              {{-- Order Status History Section (now inside .receipt-content) --}}
              <div class="mt-4">
                <h5>Order Status History</h5>
                <ul class="list-group mb-3">
                  @foreach($order->statusHistories()->orderBy('created_at')->get() as $history)
                    <li class="list-group-item">
                      <strong>{{ $history->status }}</strong>
                      by
                      @if($history->changed_by_type === 'admin')
                        {{ \App\Models\User::find($history->changed_by_id)?->name ?? 'Admin' }} (Admin)
                      @elseif($history->changed_by_type === 'staff')
                        {{ \App\Models\Employee::find($history->changed_by_id)?->name ?? 'Staff' }} (Staff)
                      @else
                        Unknown
                      @endif
                      at {{ $history->created_at->format('Y-m-d H:i') }}
                    </li>
                  @endforeach
                </ul>
              </div>
            </div>

            <div class="receipt-footer">
              <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ $order->reference_no }}" alt="QR Code">
              </div>
              <p class="thank-you">Thank you for choosing us!</p>
              <p class="keep-receipt">Please keep this receipt for your records.</p>
            </div>
          </div>

          <div id="pdf-clone-container-{{ $order->id }}" style="position: fixed; left: -9999px; top: -9999px; visibility: hidden;"></div>
          <div class="text-center mt-4">
            <button onclick="downloadReceiptAsPDF('{{ $order->id }}')" class="btn btn-primary" id="generateBtn-{{ $order->id }}">
              <i class="bi bi-download"></i> Generate Receipt
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
.receipt-container {
  max-width: 380px;
  margin: 0 auto;
  font-family: 'Courier New', Courier, monospace;
  border: 2px dashed #333;
  padding: 24px 18px;
  background: #fff;
  position: relative;
  transform: translateZ(0);
  backface-visibility: hidden;
  -webkit-font-smoothing: antialiased;
}
.receipt-header {
  text-align: center;
  margin-bottom: 20px;
  position: relative;
}
.receipt-logo {
  width: 80px;
  height: auto;
  margin-bottom: 8px;
  display: inline-block;
}
.receipt-header h1 {
  font-size: 1.2rem;
  font-weight: bold;
  letter-spacing: 2px;
  margin: 0;
  line-height: 1.2;
}
.receipt-content {
  font-size: 0.95rem;
  position: relative;
}
.receipt-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  line-height: 1.4;
}
.receipt-row.total {
  font-weight: bold;
  font-size: 1.1rem;
  margin-top: 12px;
  border-top: 1px dashed #333;
  padding-top: 8px;
}
.receipt-footer {
  text-align: center;
  margin-top: 20px;
  position: relative;
}
.qr-code {
  margin-bottom: 12px;
  display: inline-block;
}
.qr-code img {
  width: 100px;
  height: 100px;
  display: block;
  max-width: 100%;
  height: auto;
  object-fit: contain;
}
.thank-you {
  margin: 0;
  color: #444;
  line-height: 1.4;
}
.keep-receipt {
  margin: 4px 0 0;
  font-size: 0.85rem;
  color: #888;
  line-height: 1.4;
}
.btn-loading {
  position: relative;
  pointer-events: none;
  opacity: 0.8;
}
.btn-loading .spinner-border {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 1rem;
  height: 1rem;
  border-width: 0.15em;
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadReceiptAsPDF(orderId) {
  const button = document.getElementById(`generateBtn-${orderId}`);
  const originalContent = button.innerHTML;
  
  // Show loading state
  button.classList.add('btn-loading');
  button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
  button.disabled = true;

  // Clone the receipt content to a hidden container
  const receipt = document.getElementById(`printable-receipt-${orderId}`);
  const cloneContainer = document.getElementById(`pdf-clone-container-${orderId}`);
  cloneContainer.innerHTML = '';
  const clone = receipt.cloneNode(true);
  cloneContainer.appendChild(clone);

  // Configure PDF options
  const opt = {
    margin: 0.3,
    filename: `receipt-${orderId}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { 
      scale: 2,
      useCORS: true,
      logging: true, // Enable logging for debugging
      letterRendering: true,
      allowTaint: true,
      scrollY: 0,
      scrollX: 0,
      onclone: function(clonedDoc) {
        // Ensure all images are loaded in the cloned document
        const images = clonedDoc.getElementsByTagName('img');
        for (let img of images) {
          if (!img.complete) {
            img.crossOrigin = 'anonymous';
          }
        }
      }
    },
    jsPDF: { 
      unit: 'in', 
      format: 'letter', 
      orientation: 'portrait'
    }
  };

  // Function to generate PDF
  function generatePDF() {
    html2pdf().set(opt).from(clone).save()
      .then(() => {
        console.log('PDF generated successfully');
        button.classList.remove('btn-loading');
        button.innerHTML = originalContent;
        button.disabled = false;
        cloneContainer.innerHTML = '';
      })
      .catch(error => {
        console.error('PDF generation failed:', error);
        button.classList.remove('btn-loading');
        button.innerHTML = originalContent;
        button.disabled = false;
        cloneContainer.innerHTML = '';
        alert('Failed to generate PDF. Please try again.');
      });
  }

  // Wait for images to load
  const images = clone.getElementsByTagName('img');
  let loaded = 0;
  let failed = 0;

  if (images.length === 0) {
    generatePDF();
  } else {
    for (let img of images) {
      img.crossOrigin = 'anonymous';
      img.onload = () => {
        loaded++;
        if (loaded + failed === images.length) generatePDF();
      };
      img.onerror = () => {
        console.error('Failed to load image:', img.src);
        failed++;
        if (loaded + failed === images.length) generatePDF();
      };
      if (img.complete) {
        loaded++;
        if (loaded + failed === images.length) generatePDF();
      }
    }
  }
}
</script>
@endpush 