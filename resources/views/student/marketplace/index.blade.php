@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">

   <div class="pageTitle">
      <h2>My Marketplace</h2>
      <ol class="breadcrumb">
         <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
         <li class="breadcrumb-item active">My Marketplace</li>
      </ol>
   </div>

   <div class="my-marketplace-page">

      <div class="mkt-tabs">
         <span class="mkt-tab mkt-tab--active">My Products</span>
      </div>

      <div class="mkt-products-section">
         <div class="mkt-products-header">
            <div class="mkt-products-heading">
               <span class="mkt-products-icon"><i class="fas fa-shopping-bag"></i></span>
               <div>
                  <h3>My Products <span class="mkt-products-count">({{ $publishedCount }}/{{ $products->count() }})</span></h3>
                  <p>Manage your products and share your amazing creations with the world.</p>
               </div>
            </div>
         </div>

         <div class="mkt-products-scroll">
            <div class="mkt-products-grid" id="mkt-products-grid">
               @forelse($products as $product)
                  @php
                     $isPublished = (int) $product->status === 1;
                     $randomImage = $product->images->isNotEmpty() ? $product->images->random() : null;
                  @endphp
                  <div class="mkt-product-card" data-product-id="{{ $product->id }}">
                     <div class="mkt-product-image-wrap">
                        @if($randomImage)
                           <img src="{{ $randomImage->url }}" alt="" class="mkt-product-image">
                        @else
                           <div class="mkt-product-image-placeholder"><i class="fas fa-image"></i></div>
                        @endif
                     </div>
                     <div class="mkt-product-body">
                        <div class="mkt-product-title">{{ \Illuminate\Support\Str::limit($product->name, 40) }}</div>
                        <span class="mkt-badge {{ $isPublished ? 'mkt-badge--published' : 'mkt-badge--draft' }}">
                           {{ $isPublished ? 'Published' : 'Draft' }}
                        </span>
                     </div>
                     <div class="mkt-product-actions">
                        <button type="button" class="mkt-product-action-btn" title="View"><i class="fas fa-eye"></i></button>
                        <button type="button" class="mkt-product-action-btn" title="Share"><i class="fas fa-share-alt"></i></button>
                        <div class="mkt-product-menu">
                           <button type="button" class="mkt-product-action-btn mkt-product-menu-toggle" title="More"><i class="fas fa-ellipsis-v"></i></button>
                           <div class="mkt-product-menu-dropdown">
                              <button type="button" class="mkt-product-menu-item mkt-edit-product"><i class="fas fa-pen"></i> Edit</button>
                              <button type="button" class="mkt-product-menu-item mkt-delete-product"><i class="fas fa-trash"></i> Delete</button>
                           </div>
                        </div>
                     </div>
                  </div>
               @empty
                  <div class="mkt-empty-state">
                     <i class="fas fa-shopping-bag"></i>
                     <p>No product available.</p>
                  </div>
               @endforelse
            </div>
            <button type="button" class="mkt-products-scroll-btn mkt-products-scroll-btn--prev" id="mkt-products-scroll-prev" title="See previous products" hidden>
               <i class="fas fa-chevron-left"></i>
            </button>
            <button type="button" class="mkt-products-scroll-btn mkt-products-scroll-btn--next" id="mkt-products-scroll-next" title="See more products" hidden>
               <i class="fas fa-chevron-right"></i>
            </button>
         </div>
      </div>

      <div class="mkt-add-section" id="mkt-add-products">
         <div class="mkt-add-header">
            <span class="mkt-products-icon"><i class="fas fa-shopping-bag"></i></span>
            <div>
               <h3 id="mkt-add-section-title">Add Products</h3>
            </div>
            <span class="mkt-add-progress">{{ $products->count() }} Products Added</span>
            <small class="mkt-form-hint" id="mkt-product-limit-note">
               {{ $products->count() >= $maxProducts ? 'You have reached the ' . $maxProducts . ' product limit. You can still edit existing products.' : 'You can add up to ' . $maxProducts . ' products.' }}
            </small>
         </div>

         <div class="mkt-add-body">
            <div class="mkt-add-steps">
               <div class="mkt-add-step mkt-add-step--active" id="mkt-step-1">
                  <span class="mkt-add-step-num">1</span>
                  <div>
                     <strong>Marketplace Details</strong>
                     <p>Name your marketplace and share your story</p>
                  </div>
               </div>
               <div class="mkt-add-step-connector"></div>
               <div class="mkt-add-step" id="mkt-step-2">
                  <span class="mkt-add-step-num">2</span>
                  <div>
                     <strong>Add Product(s)</strong>
                     <p>Add your product details, pricing and more</p>
                  </div>
               </div>
            </div>

            <form class="mkt-add-form" id="mkt-product-form" action="{{ route('student.marketplace.save') }}" method="POST" enctype="multipart/form-data">
               @csrf
               <input type="hidden" name="id" id="mkt-product-id" value="">
               <div class="mkt-add-form-intro-row">
                  <p class="mkt-add-form-intro">Fill out the form below to get started</p>
                  <button type="button" class="mkt-btn-reset" id="mkt-reset-form">Reset</button>
               </div>

               <div class="mkt-form-field">
                  <label><span class="mkt-form-field-num">1</span> Name for Your Marketplace</label>
                  <div class="mkt-input-wrap">
                     <input type="text" name="name" class="form-control mkt-counted-input" maxlength="60" placeholder="e.g., Ananya's Creative Corner" value="{{ old('name') }}" required>
                     <span class="mkt-char-count">0/60</span>
                  </div>
               </div>

               <div class="mkt-form-field">
                  <label><span class="mkt-form-field-num">2</span> Select Currency</label>
                  <select name="currency_id" class="form-control" required>
                     <option value="">Select Currency</option>
                     @foreach($currencies as $currency)
                        <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                           {{ $currency->code }}
                        </option>
                     @endforeach
                  </select>
                  <small class="mkt-form-hint">This will be used for all prices in your marketplace.</small>
               </div>

               <div class="mkt-form-field">
                  <label><span class="mkt-form-field-num">3</span> Tell Your Story</label>
                  <div class="mkt-textarea-wrap">
                     <textarea name="story" class="form-control mkt-counted-textarea" maxlength="500" rows="3" placeholder="Share your journey, inspiration and what you love to create..." required>{{ old('story') }}</textarea>
                     <!-- <button type="button" class="mkt-btn-ai-inline"><i class="fas fa-magic"></i> Enhance with AI</button> -->
                     <span class="mkt-char-count">0/500</span>
                  </div>
               </div>

               <div class="mkt-add-form-actions">
                  <button type="button" class="mkt-btn-save" id="mkt-save-continue">Save &amp; Continue <i class="fas fa-arrow-right"></i></button>
               </div>

               <div id="mkt-product-section" hidden>
                  <hr class="mkt-add-divider">

                  <h4 class="mkt-add-products-title">Add Product(s)</h4>

                  <div class="mkt-product-fields">
                     <div class="mkt-form-field">
                        <label>Upload Product Image</label>
                        <label class="mkt-dropzone" id="mkt-dropzone">
                           <input type="file" name="product_images[]" id="mkt-product-images" accept=".png,.jpg,.jpeg,.webp" multiple hidden>
                           <i class="fas fa-cloud-upload-alt"></i>
                           <span>Drag and drop or click to upload</span>
                           <small>PNG, JPG or WEBP (Max 5MB)</small>
                        </label>
                        <div class="mkt-selected-files" id="mkt-existing-files"></div>
                        <div id="mkt-removed-image-inputs" hidden></div>
                        <div class="mkt-selected-files" id="mkt-selected-files"></div>
                     </div>

                     <div class="mkt-form-field">
                        <label>Add Product Description</label>
                        <div class="mkt-textarea-wrap">
                           <textarea name="description" class="form-control mkt-counted-textarea" maxlength="500" rows="4" placeholder="Tell us about your product..." required></textarea>
                           <span class="mkt-char-count">0/500</span>
                        </div>
                     </div>

                     <div class="mkt-form-field">
                        <label>What makes your product special?</label>
                        <div class="mkt-textarea-wrap">
                           <textarea name="special_feature" class="form-control mkt-counted-textarea" maxlength="300" rows="4" placeholder="Share what makes your product unique..." required></textarea>
                           <!-- <button type="button" class="mkt-btn-ai-inline"><i class="fas fa-magic"></i> Enhance using AI</button> -->
                           <span class="mkt-char-count">0/300</span>
                        </div>
                     </div>

                     <div class="mkt-form-field">
                        <label>Price</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="Enter price" required>
                        <small class="mkt-form-hint">Uses your marketplace currency.</small>
                     </div>
                  </div>

                  <div class="mkt-locked-row">
                     <i class="fas fa-lock"></i>
                     <div>
                        <strong>Publish</strong>
                        <p>Make your product visible in the marketplace.</p>
                     </div>
                  </div>
                  <div class="mkt-locked-row">
                     <i class="fas fa-lock"></i>
                     <div>
                        <strong>Share Link</strong>
                        <p>Get a shareable link to your product.</p>
                     </div>
                  </div>

                  <div class="mkt-add-form-actions">
                     <button type="submit" class="mkt-btn-save" id="mkt-submit-btn">Save</button>
                  </div>
               </div>
            </form>
         </div>
      </div>

   </div>

   <div class="mkt-limit-toast" id="mkt-limit-toast" hidden>Maximum character limit reached.</div>
</div>

<script>
(function () {
   'use strict';

   // ---- Element references ----
   var grid = document.querySelector('.mkt-products-grid');
   var scrollPrevBtn = document.getElementById('mkt-products-scroll-prev');
   var scrollNextBtn = document.getElementById('mkt-products-scroll-next');
   var productsCount = document.querySelector('.mkt-products-count');
   var addProgress = document.querySelector('.mkt-add-progress');
   var productLimitNote = document.getElementById('mkt-product-limit-note');
   var form = document.getElementById('mkt-product-form');
   var productSection = document.getElementById('mkt-product-section');
   var saveContinueBtn = document.getElementById('mkt-save-continue');
   var step1 = document.getElementById('mkt-step-1');
   var step2 = document.getElementById('mkt-step-2');
   var submitBtn = document.getElementById('mkt-submit-btn');
   var resetFormBtn = document.getElementById('mkt-reset-form');
   var addSectionTitle = document.getElementById('mkt-add-section-title');
   var existingFiles = document.getElementById('mkt-existing-files');
   var removedImageInputs = document.getElementById('mkt-removed-image-inputs');
   var imageInput = document.getElementById('mkt-product-images');
   var dropzone = document.getElementById('mkt-dropzone');
   var selectedFilesList = document.getElementById('mkt-selected-files');
   var limitToast = document.getElementById('mkt-limit-toast');

   var productsUrl = "{{ route('student.marketplace.products') }}";
   var editUrlTemplate = "{{ route('student.marketplace.edit', ['id' => '__ID__']) }}";
   var deleteUrlTemplate = "{{ route('student.marketplace.destroy', ['id' => '__ID__']) }}";
   var publishUrlTemplate = "{{ route('student.marketplace.publish', ['id' => '__ID__']) }}";
   var downloadUrlTemplate = "{{ route('student.marketplace.image.download', ['id' => '__ID__']) }}";
   var maxProducts = {{ (int) $maxProducts }};
   var currentProductTotal = {{ $products->count() }};
   var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

   // ---- Small helpers ----
   function escapeHtml(text) {
      var div = document.createElement('div');
      div.textContent = text == null ? '' : String(text);
      return div.innerHTML;
   }

   function setFieldValue(name, value) {
      var field = form.querySelector('[name="' + name + '"]');
      if (!field) return;
      field.value = value == null ? '' : value;
      field.dispatchEvent(new Event('input'));
   }

   function closeAllDropdowns() {
      document.querySelectorAll('.mkt-product-menu-dropdown--open').forEach(function (el) {
         el.classList.remove('mkt-product-menu-dropdown--open');
      });
   }

   function scrollToPageTop() {
      window.scrollTo({
         top: 0,
         behavior: 'smooth'
      });
   }

   function isEditingProduct() {
      return form.querySelector('#mkt-product-id').value !== '';
   }

   function updateProductLimitState() {
      var limitReached = currentProductTotal >= maxProducts;
      var shouldBlockNewProduct = limitReached && !isEditingProduct();

      saveContinueBtn.disabled = shouldBlockNewProduct;
      saveContinueBtn.setAttribute('aria-disabled', shouldBlockNewProduct ? 'true' : 'false');

      if (productLimitNote) {
         productLimitNote.textContent = limitReached
            ? ('You have reached the ' + maxProducts + ' product limit. You can still edit existing products.')
            : ('You can add up to ' + maxProducts + ' products.');
      }
   }

   // ---- "Max length reached" toast, shared by every counted field ----
   var limitToastTimer = null;
   function showLimitToast() {
      if (!limitToast) return;
      limitToast.hidden = false;
      limitToast.classList.add('mkt-limit-toast--visible');
      clearTimeout(limitToastTimer);
      limitToastTimer = setTimeout(function () {
         limitToast.classList.remove('mkt-limit-toast--visible');
      }, 2200);
   }

   // ---- Character counters (textareas + the Name input) ----
   document.querySelectorAll('.mkt-counted-textarea, .mkt-counted-input').forEach(function (field) {
      var counter = field.parentElement.querySelector('.mkt-char-count');
      if (!counter) return;
      var max = Number(field.getAttribute('maxlength'));
      var update = function (e) {
         var atLimit = field.value.length >= max;
         counter.textContent = field.value.length + '/' + max;
         counter.classList.toggle('mkt-char-count--limit', atLimit);
         // Only alert for real typing/paste, not when a value is set programmatically (e.g. prefilling the Edit form).
         if (atLimit && e && e.isTrusted) showLimitToast();
      };
      field.addEventListener('input', update);
      update();
   });

   // ---- Multi-image staging (accumulate across picks/drops, remove individually) ----
   var stagedFiles = new DataTransfer();
   var ALLOWED_IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];
   var MAX_IMAGE_SIZE_BYTES = 5 * 1024 * 1024;

   function imageFileError(file) {
      var extension = file.name.split('.').pop().toLowerCase();
      if (ALLOWED_IMAGE_EXTENSIONS.indexOf(extension) === -1) {
         return 'unsupported file type';
      }
      if (file.size > MAX_IMAGE_SIZE_BYTES) {
         return 'larger than 5MB';
      }
      return null;
   }

   function renderStagedFiles() {
      selectedFilesList.innerHTML = '';
      Array.prototype.forEach.call(stagedFiles.files, function (file, index) {
         var chip = document.createElement('div');
         chip.className = 'mkt-file-chip';
         chip.innerHTML = '<span class="mkt-file-chip-name">' + escapeHtml(file.name) + '</span>'
            + '<button type="button" class="mkt-file-chip-remove" data-index="' + index + '">&times;</button>';
         selectedFilesList.appendChild(chip);
      });
      imageInput.files = stagedFiles.files;
   }

   function addStagedFiles(fileList) {
      var rejected = [];

      Array.prototype.forEach.call(fileList, function (file) {
         var error = imageFileError(file);
         if (error) {
            rejected.push(file.name + ' (' + error + ')');
            return;
         }
         stagedFiles.items.add(file);
      });

      renderStagedFiles();

      if (rejected.length) {
         swal(
            'Skipped: ' + rejected.join(', ') + '. Only PNG, JPG, JPEG or WEBP images up to 5MB are allowed.',
            { icon: 'warning' }
         );
      }
   }

   function resetStagedFiles() {
      stagedFiles = new DataTransfer();
      renderStagedFiles();
   }

   imageInput.addEventListener('change', function () { addStagedFiles(imageInput.files); });

   selectedFilesList.addEventListener('click', function (e) {
      if (!e.target.classList.contains('mkt-file-chip-remove')) return;
      var index = Number(e.target.dataset.index);
      var remaining = new DataTransfer();
      Array.prototype.forEach.call(stagedFiles.files, function (file, i) {
         if (i !== index) remaining.items.add(file);
      });
      stagedFiles = remaining;
      renderStagedFiles();
   });

   ['dragover', 'dragenter'].forEach(function (evt) {
      dropzone.addEventListener(evt, function (e) {
         e.preventDefault();
         dropzone.classList.add('mkt-dropzone--active');
      });
   });
   ['dragleave', 'drop'].forEach(function (evt) {
      dropzone.addEventListener(evt, function (e) {
         e.preventDefault();
         dropzone.classList.remove('mkt-dropzone--active');
      });
   });
   dropzone.addEventListener('drop', function (e) {
      if (e.dataTransfer.files.length) addStagedFiles(e.dataTransfer.files);
   });

   // ---- Products grid: render from JSON + AJAX refresh (single source of truth) ----
   function productCardHtml(product) {
      var isPublished = Number(product.status) === 1;
      var name = product.name || '';
      var title = escapeHtml(name.length > 40 ? name.slice(0, 40) + '...' : name);
      var images = product.images || [];
      var randomImage = images.length ? images[Math.floor(Math.random() * images.length)] : null;

      var imageHtml = randomImage
         ? '<img src="' + randomImage.url + '" alt="" class="mkt-product-image">'
         : '<div class="mkt-product-image-placeholder"><i class="fas fa-image"></i></div>';

      return '<div class="mkt-product-card" data-product-id="' + product.id + '">'
         + '<div class="mkt-product-image-wrap">' + imageHtml + '</div>'
         + '<div class="mkt-product-body">'
         +    '<div class="mkt-product-title">' + title + '</div>'
         +    '<span class="mkt-badge ' + (isPublished ? 'mkt-badge--published' : 'mkt-badge--draft') + '">'
         +       (isPublished ? 'Published' : 'Draft')
         +    '</span>'
         + '</div>'
         + '<div class="mkt-product-actions">'
         +    '<button type="button" class="mkt-product-action-btn" title="View"><i class="fas fa-eye"></i></button>'
         +    '<button type="button" class="mkt-product-action-btn" title="Share"><i class="fas fa-share-alt"></i></button>'
         +    '<div class="mkt-product-menu">'
         +       '<button type="button" class="mkt-product-action-btn mkt-product-menu-toggle" title="More"><i class="fas fa-ellipsis-v"></i></button>'
         +       '<div class="mkt-product-menu-dropdown">'
         +          '<button type="button" class="mkt-product-menu-item mkt-edit-product"><i class="fas fa-pen"></i> Edit</button>'
         +          '<button type="button" class="mkt-product-menu-item mkt-delete-product"><i class="fas fa-trash"></i> Delete</button>'
         +       '</div>'
         +    '</div>'
         + '</div>'
         + '</div>';
   }

   function updateScrollButtons() {
      var hasOverflow = grid.scrollWidth > grid.clientWidth + 1;
      var atStart = grid.scrollLeft <= 1;
      var atEnd = grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 1;

      if (scrollPrevBtn) scrollPrevBtn.hidden = !hasOverflow || atStart;
      if (scrollNextBtn) scrollNextBtn.hidden = !hasOverflow || atEnd;
   }

   function renderProducts(payload) {
      var products = payload.products || [];
      currentProductTotal = Number(payload.total || 0);
      grid.innerHTML = products.length
         ? products.map(productCardHtml).join('')
         : '<div class="mkt-empty-state"><i class="fas fa-shopping-bag"></i><p>No product available.</p></div>';

      if (productsCount) productsCount.textContent = '(' + payload.published + '/' + payload.total + ')';
      if (addProgress) addProgress.textContent = payload.total + ' Products Added';
      updateProductLimitState();
      updateScrollButtons();
   }

   function refreshProducts() {
      fetch(productsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
         .then(function (res) { return res.json(); })
         .then(function (data) {
            if (data.success) renderProducts(data.data);
         });
   }

   if (scrollNextBtn || scrollPrevBtn) {
      if (scrollNextBtn) {
         scrollNextBtn.addEventListener('click', function () {
            grid.scrollBy({ left: grid.clientWidth * 0.8, behavior: 'smooth' });
         });
      }
      if (scrollPrevBtn) {
         scrollPrevBtn.addEventListener('click', function () {
            grid.scrollBy({ left: -grid.clientWidth * 0.8, behavior: 'smooth' });
         });
      }
      grid.addEventListener('scroll', updateScrollButtons);
      window.addEventListener('resize', updateScrollButtons);
      updateScrollButtons();
   }

   // ---- Save & Continue -> reveal Add Product section ----
   saveContinueBtn.addEventListener('click', function () {
      if (currentProductTotal >= maxProducts && !isEditingProduct()) {
         swal('You can add up to ' + maxProducts + ' products only.', { icon: 'warning' });
         return;
      }

      var valid = true;
      form.querySelectorAll('[name="name"], [name="currency_id"], [name="story"]').forEach(function (field) {
         if (!field.reportValidity()) valid = false;
      });
      if (!valid) return;

      productSection.hidden = false;
      saveContinueBtn.hidden = true;
      step2.classList.add('mkt-add-step--active');
      productSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
   });

   // ---- Reset the form back to a blank "add" state ----
   function resetForm() {
      form.reset();
      form.querySelector('#mkt-product-id').value = '';
      document.querySelectorAll('.mkt-counted-textarea, .mkt-counted-input').forEach(function (t) {
         t.dispatchEvent(new Event('input'));
      });
      submitBtn.textContent = 'Save';
      if (addSectionTitle) addSectionTitle.textContent = 'Add Products';
      productSection.hidden = true;
      saveContinueBtn.hidden = false;
      step1.classList.add('mkt-add-step--active');
      step2.classList.remove('mkt-add-step--active');
      existingFiles.innerHTML = '';
      removedImageInputs.innerHTML = '';
      resetStagedFiles();
      updateProductLimitState();
   }

   // ---- Remove an already-uploaded image while editing ----
   existingFiles.addEventListener('click', function (e) {
      var removeBtn = e.target.closest('.mkt-existing-file-remove');
      if (!removeBtn) return;

      var imageId = removeBtn.dataset.imageId;
      var chip = removeBtn.closest('.mkt-file-chip');
      if (chip) chip.remove();

      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'remove_image_ids[]';
      input.value = imageId;
      removedImageInputs.appendChild(input);
   });

   if (resetFormBtn) {
      resetFormBtn.addEventListener('click', resetForm);
   }

   // ---- Single AJAX function backing both create and edit ----
   var isSubmitting = false;

   function setSubmitLoading(isLoading, label) {
      submitBtn.disabled = isLoading;
      submitBtn.innerHTML = isLoading
         ? '<i class="fas fa-spinner fa-spin"></i> ' + (label === 'Update' ? 'Updating...' : 'Saving...')
         : label;
   }

   form.addEventListener('submit', function (e) {
      e.preventDefault();

      if (isSubmitting) return;

      var hasExistingImages = existingFiles.children.length > 0;
      var hasStagedImages = stagedFiles.files.length > 0;
      if (!hasExistingImages && !hasStagedImages) {
         swal('Please upload at least one product image.', { icon: 'warning' });
         dropzone.scrollIntoView({ behavior: 'smooth', block: 'center' });
         return;
      }

      var wasEdit = form.querySelector('#mkt-product-id').value !== '';
      var label = wasEdit ? 'Update' : 'Save';

      isSubmitting = true;
      setSubmitLoading(true, label);

      fetch(form.action, {
         method: 'POST',
         headers: { 'X-Requested-With': 'XMLHttpRequest' },
         body: new FormData(form),
      })
         .then(function (res) { return res.json(); })
         .then(function (data) {
            isSubmitting = false;
            setSubmitLoading(false, label);
            if (!data.success) {
               swal(data.message || 'Something went wrong.', { icon: 'error' });
               return;
            }
            resetForm();
            refreshProducts();
            scrollToPageTop();
            swal(wasEdit ? 'Product updated successfully.' : 'Product added successfully.', { icon: 'success' });
         })
         .catch(function () {
            isSubmitting = false;
            setSubmitLoading(false, label);
            swal('Something went wrong.', { icon: 'error' });
         });
   });

   // ---- Fill the form for editing an existing product ----
   function fillFormForEdit(product) {
      form.querySelector('#mkt-product-id').value = product.id;
      setFieldValue('name', product.name);
      setFieldValue('currency_id', product.currency_id);
      setFieldValue('story', product.story);
      setFieldValue('description', product.description);
      setFieldValue('special_feature', product.special_feature);
      setFieldValue('price', product.price);

      resetStagedFiles();

      existingFiles.innerHTML = '';
      removedImageInputs.innerHTML = '';
      (product.images || []).forEach(function (image) {
         var chip = document.createElement('div');
         chip.className = 'mkt-file-chip';
         chip.dataset.imageId = image.id;
         chip.innerHTML = '<span class="mkt-file-chip-name">' + escapeHtml(image.image_path.split('/').pop()) + '</span>'
            + '<span class="mkt-file-chip-actions">'
            + '<a href="' + downloadUrlTemplate.replace('__ID__', image.id) + '" class="mkt-file-chip-icon-btn" title="Download"><i class="fas fa-download"></i></a>'
            + '<button type="button" class="mkt-file-chip-icon-btn mkt-existing-file-remove" data-image-id="' + image.id + '" title="Remove"><i class="fas fa-times"></i></button>'
            + '</span>';
         existingFiles.appendChild(chip);
      });

      submitBtn.textContent = 'Update';
      if (addSectionTitle) addSectionTitle.textContent = 'Edit Products';
      productSection.hidden = true;
      saveContinueBtn.hidden = false;
      step2.classList.remove('mkt-add-step--active');
      updateProductLimitState();
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
   }

   // ---- Product card menu: toggle dropdown, edit, delete (event delegation so re-rendered cards stay wired) ----
   document.addEventListener('click', closeAllDropdowns);

   grid.addEventListener('click', function (e) {
      var toggle = e.target.closest('.mkt-product-menu-toggle');
      if (toggle) {
         e.stopPropagation();
         var dropdown = toggle.nextElementSibling;
         var isOpen = dropdown.classList.contains('mkt-product-menu-dropdown--open');
         closeAllDropdowns();
         if (!isOpen) dropdown.classList.add('mkt-product-menu-dropdown--open');
         return;
      }

      var editBtn = e.target.closest('.mkt-edit-product');
      if (editBtn) {
         var editId = editBtn.closest('.mkt-product-card').dataset.productId;

         fetch(editUrlTemplate.replace('__ID__', editId), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
               if (!data.success) {
                  swal(data.message || 'Unable to load product.', { icon: 'error' });
                  return;
               }
               fillFormForEdit(data.product);
            })
            .catch(function () { swal('Unable to load product.', { icon: 'error' }); });
         return;
      }

      var deleteBtn = e.target.closest('.mkt-delete-product');
      if (deleteBtn) {
         var deleteId = deleteBtn.closest('.mkt-product-card').dataset.productId;

         swal({
            title: 'Are you sure?',
            text: 'This product will be permanently deleted.',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
         }).then(function (willDelete) {
            if (!willDelete) return;

            fetch(deleteUrlTemplate.replace('__ID__', deleteId), {
               method: 'DELETE',
               headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            })
               .then(function (res) { return res.json(); })
               .then(function (data) {
                  if (!data.success) {
                     swal(data.message || 'Unable to delete product.', { icon: 'error' });
                     return;
                  }
                  // If the deleted product is the one currently loaded in the form
                  // (e.g. edited, then deleted), reset the form back to blank "Add" state.
                  if (form.querySelector('#mkt-product-id').value === String(deleteId)) {
                     resetForm();
                  }
                  refreshProducts();
                  swal('Product deleted successfully.', { icon: 'success' });
               })
               .catch(function () { swal('Unable to delete product.', { icon: 'error' }); });
         });
         return;
      }

      var publishBtn = e.target.closest('.mkt-publish-product');
      if (publishBtn) {
         var publishId = publishBtn.closest('.mkt-product-card').dataset.productId;

         swal({
            title: 'Publish this product?',
            text: 'Are you sure you want to publish it? It will be visible in the marketplace.',
            icon: 'warning',
            buttons: ['Cancel', 'Publish'],
         }).then(function (willPublish) {
            if (!willPublish) return;

            fetch(publishUrlTemplate.replace('__ID__', publishId), {
               method: 'PATCH',
               headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            })
               .then(function (res) { return res.json(); })
               .then(function (data) {
                  if (!data.success) {
                     swal(data.message || 'Unable to publish product.', { icon: 'error' });
                     return;
                  }
                  refreshProducts();
                  swal(data.message, { icon: 'success' });
               })
               .catch(function () { swal('Unable to publish product.', { icon: 'error' }); });
         });
      }
   });

   updateProductLimitState();
})();
</script>

@endsection
