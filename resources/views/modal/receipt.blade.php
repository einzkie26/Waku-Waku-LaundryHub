@props(['order'])

<div class="modal fade" id="receiptModal-{{ $order->id }}" tabindex="-1" aria-labelledby="receiptModalLabel-{{ $order->id }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="receiptModalLabel-{{ $order->id }}">Receipt — {{ $order->reference_no }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="printable-receipt-{{ $order->id }}" class="receipt-container">
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
              <span>Weight:</span>
              <span>{{ $order->weight }} kg</span>
            </div>
            <div class="receipt-row total">
              <span>Total:</span>
              <span>₱{{ number_format($order->total_price, 2) }}</span>
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
        <!-- Hidden container for PDF generation -->
        <div id="pdf-clone-container-{{ $order->id }}" style="position: fixed; left: -9999px; top: -9999px; visibility: hidden;"></div>
      </div>
      <div class="modal-footer border-0">
        <button onclick="downloadReceiptAsPDF('{{ $order->id }}')" class="btn btn-primary" id="generateBtn-{{ $order->id }}">
          <i class="bi bi-download"></i> Generate Receipt
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<style>
.modal-dialog {
  max-width: 500px;
  margin: 1.75rem auto;
  transform: none !important;
}

.modal-content {
  position: relative;
  border-radius: 15px;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  background-color: #fff;
  border: none;
}

.modal-header {
  padding: 1.5rem 1.5rem 0.5rem;
  background: transparent;
}

.modal-body {
  padding: 1rem 1.5rem;
  position: relative;
  overflow: hidden;
  min-height: 200px;
}

.modal-footer {
  padding: 1rem 1.5rem 1.5rem;
  background: transparent;
}

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
  /* Remove any transition or hover effect */
  transition: none !important;
}

/* Disable all transitions and pointer events when .no-anim is applied */
.receipt-container.no-anim, .modal-content.no-anim, .modal-dialog.no-anim {
  transition: none !important;
  pointer-events: none !important;
  box-shadow: none !important;
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

.modal.fade .modal-dialog {
  transition: transform 0.2s ease-out;
}

.modal.show .modal-dialog {
  transform: none;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@push('scripts')
<script>
function downloadReceiptAsPDF(orderId) {
  const button = document.getElementById(`generateBtn-${orderId}`);
  const originalContent = button.innerHTML;
  const receipt = document.getElementById(`printable-receipt-${orderId}`);
  // Add .no-anim to prevent hover/transition glitches
  receipt.classList.add('no-anim');
  // Show loading state
  button.classList.add('btn-loading');
  button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
  button.disabled = true;

  // Clone the receipt content to a hidden container
  const cloneContainer = document.getElementById(`pdf-clone-container-${orderId}`);
  cloneContainer.innerHTML = '';
  const clone = receipt.cloneNode(true);
  cloneContainer.appendChild(clone);

  const opt = {
    margin: 0.3,
    filename: `receipt-${orderId}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { 
      scale: 2,
      useCORS: true,
      logging: false,
      letterRendering: true,
      allowTaint: true,
      scrollY: 0,
      scrollX: 0
    },
    jsPDF: { 
      unit: 'in', 
      format: 'letter', 
      orientation: 'portrait'
    }
  };

  // Wait for images (like QR code) to load in the clone
  const images = clone.getElementsByTagName('img');
  let loaded = 0;
  if (images.length === 0) {
    generatePDF();
  } else {
    for (let img of images) {
      img.onload = img.onerror = () => {
        loaded++;
        if (loaded === images.length) generatePDF();
      };
      if (img.complete) {
        loaded++;
        if (loaded === images.length) generatePDF();
      }
    }
  }
  function generatePDF() {
    html2pdf().set(opt).from(clone).save()
      .then(() => {
        // Restore button state and remove .no-anim
        button.classList.remove('btn-loading');
        button.innerHTML = originalContent;
        button.disabled = false;
        cloneContainer.innerHTML = '';
        receipt.classList.remove('no-anim');
      })
      .catch(error => {
        console.error('PDF generation failed:', error);
        button.classList.remove('btn-loading');
        button.innerHTML = originalContent;
        button.disabled = false;
        cloneContainer.innerHTML = '';
        receipt.classList.remove('no-anim');
        alert('Failed to generate PDF. Please try again.');
      });
  }
}
</script>
@endpush

