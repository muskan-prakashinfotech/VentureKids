@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
  <div class="pageTitle">
    <h2>Build Your Business Plan in Just 10 Easy Steps</h2>
  </div>

  <section>
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title business-tooltip-italic"
          data-toggle="tooltip"
          data-bs-trigger="hover"
          data-placement="top"
          title="A business plan is a step-by-step guide that explains what your business is, what problem it solves, what you are selling, who will buy it, and how much money you will make">
          What's a business plan?
        </h3>
        <a href="{{ route('student.my-workspace') }}" class="btn btn-sm btn-warning">
          <i class="material-icons">west</i> Back
        </a>
      </div>

     <div class="card-body">
 <h4 id="stepTitle" class="mb-3 fw-bold text-primary text-center">Generate My Business Plan</h4>
  <div class="row">
    <div class="col-md-12 text-center"> <!-- Changed from col-md-6 to col-md-12 and added text-center -->

      <!-- Single updatable bot message -->
      <div id="bot-message" class="fs-5 fw-semibold mb-3 text-nowrap d-inline-block">
        <strong>VentureKids Bot:</strong> Your business plan is getting cooked…
      </div>

      <!-- Orange round loader centered -->
      <div id="loader" class="mt-3">
        <div class="spinner-border" style="width: 3rem; height: 3rem; color: orange;" role="status"></div>
        <!-- <div class="mt-2 text-muted">Generating your PDF...</div> -->
      </div>

      <!-- PDF download button (hidden initially) -->
      <form id="pdf-form" action="{{ route('student.businessplan.download-pdf') }}" method="POST" style="display: none;">
              @csrf
              <button class="btn btn-orange mt-4">
                📎 PDF Download
              </button>
      </form>
       <div id="error-message" class="text-danger mt-3 fw-bold" style="display: none;"></div>
    </div>
  </div>
</div>

  </section>
</div>

{{-- JS --}}
<script>

  function initializeTooltips() {
    $('[data-toggle="tooltip"]').tooltip(); // Bootstrap 4 style
  }

  document.addEventListener('DOMContentLoaded', function () {
     initializeTooltips(); 
    fetch('{{ route("student.businessplan.generate-pdf-async") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
      document.getElementById('loader').style.display = 'none';

      if (data.success) {
        document.getElementById('bot-message').innerHTML =
          '<strong>VentureKids Bot:</strong> Awesome work! Here’s your business plan ready to download.';
        document.getElementById('pdf-form').style.display = 'block';
         initializeTooltips(); 
      } else {
        document.getElementById('error-message').innerText = data.message || 'Something went wrong.';
        document.getElementById('error-message').style.display = 'block';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      document.getElementById('loader').style.display = 'none';
      document.getElementById('error-message').innerText = 'Failed to generate PDF.';
      document.getElementById('error-message').style.display = 'block';
    });
  });
</script>
@endsection