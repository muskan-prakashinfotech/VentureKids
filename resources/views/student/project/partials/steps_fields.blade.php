@php
   $questionIcons = ['fas fa-lightbulb', 'fas fa-compass', 'fas fa-users', 'fas fa-rocket', 'fas fa-flask', 'fas fa-star', 'fas fa-comments', 'fas fa-map-signs'];

   $acceptForTypes = function ($allowedTypes) {
      $map = ['pdf' => '.pdf', 'images' => 'image/*', 'videos' => 'video/*'];
      $types = array_filter(explode(',', (string) $allowedTypes));

      return implode(',', array_map(fn ($type) => $map[$type] ?? '', $types));
   };

   $extensionsForTypes = function ($allowedTypes) {
      $map = [
         'pdf' => ['pdf'],
         'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
         'videos' => ['mp4', 'mov', 'avi', 'mkv'],
      ];
      $types = array_filter(explode(',', (string) $allowedTypes));
      $extensions = [];

      foreach ($types as $type) {
         $extensions = array_merge($extensions, $map[$type] ?? []);
      }

      return implode(',', $extensions);
   };

   $chipIconForType = function ($fileType) {
      $map = ['videos' => 'fas fa-film', 'images' => 'fas fa-image', 'pdf' => 'fas fa-file-pdf'];

      return $map[$fileType] ?? 'fas fa-paperclip';
   };

   $nonVideoTypes = function ($allowedTypes) {
      return implode(',', array_diff(explode(',', (string) $allowedTypes), ['videos']));
   };
@endphp

@if(!$currentSection)
   <div class="cp-empty-state">No sections have been configured yet. Please check back later.</div>
@else
   <h3 class="cp-section-title">{{ $currentSection->section_title }}</h3>

   @foreach($currentSection->questions as $qIndex => $question)
      @php
         $icon = $questionIcons[$qIndex % count($questionIcons)];
         $existingFiles = $attachments->get($question->id, collect());
         $allowsVideo = in_array('videos', array_filter(explode(',', (string) $question->allowed_types)));
         $questionNonVideoTypes = $nonVideoTypes($question->allowed_types);
      @endphp
      <div class="cp-field-card">
         <div class="cp-field-icon" style="background:#FCEBDD;"><i class="{{ $icon }}" style="color:#F2994A;"></i></div>
         <div class="cp-field-body">
            <div class="cp-field-label">
               <span class="cp-field-num">{{ $qIndex + 1 }}</span> {{ $question->field_text }}
            </div>
            @if($question->help_text)
               <p class="cp-field-caption">{{ $question->help_text }}</p>
            @endif

            @if($question->field_type !== 'file')
               <textarea name="answers[{{ $question->id }}]" class="cp-input cp-textarea" rows="4" data-required="{{ $question->is_required ? '1' : '0' }}">{{ old('answers.'.$question->id, $answers->get($question->id)) }}</textarea>
               @error('answers.'.$question->id)<small class="cp-error">{{ $message }}</small>@enderror
            @endif

            @if($question->allow_attachments)
               <div class="cp-attachment-block" data-question-id="{{ $question->id }}" data-required="{{ $question->is_required ? '1' : '0' }}">
                  <div class="cp-file-chips">
                     @foreach($existingFiles as $file)
                        <span class="cp-file-chip" data-attachment-id="{{ $file->id }}">
                           <i class="{{ $chipIconForType($file->file_type) }}"></i>
                           <span class="cp-file-chip-name" title="{{ $file->file_name }}">{{ $file->file_name }}</span>
                           @if($file->file_type === 'videos')
                              <a href="{{ $file->url }}" target="_blank" rel="noopener" class="cp-file-chip-action" title="Open video">
                                 <i class="fas fa-external-link-alt"></i>
                              </a>
                           @else
                              <a href="{{ route('student.download-attachment', $file->id) }}" class="cp-file-chip-action" title="Download file">
                                 <i class="fas fa-download"></i>
                              </a>
                           @endif
                           <button type="button" class="cp-file-chip-remove" title="Remove file">&times;</button>
                        </span>
                     @endforeach
                  </div>

                  @if($questionNonVideoTypes !== '')
                     <div class="cp-file-inputs"
                        data-question-id="{{ $question->id }}"
                        data-allowed-extensions="{{ $extensionsForTypes($questionNonVideoTypes) }}">
                        <div class="cp-file-input-row">
                           <div class="custom-file">
                              <input type="file" name="attachments[{{ $question->id }}][]" class="custom-file-input cp-file-input" accept="{{ $acceptForTypes($questionNonVideoTypes) }}">
                              <label class="custom-file-label">Choose File</label>
                           </div>
                        </div>
                     </div>

                     <small class="cp-file-hint">Max file size: 10MB</small>
                     @error('attachments.'.$question->id)<small class="cp-error">{{ $message }}</small>@enderror

                     @if($question->allowed_multiples)
                        <button type="button" class="cp-add-more-file" data-question-id="{{ $question->id }}" data-accept="{{ $acceptForTypes($questionNonVideoTypes) }}">
                           <i class="fas fa-plus"></i> Add more file
                        </button>
                     @endif
                  @endif

                  @if($allowsVideo)
                     @php
                        $oldVideoUrls = old('video_urls.'.$question->id) ?: [''];
                     @endphp
                     <div class="cp-video-url-inputs" data-question-id="{{ $question->id }}">
                        @foreach($oldVideoUrls as $videoUrlValue)
                           <div class="cp-file-input-row">
                              <input type="url" name="video_urls[{{ $question->id }}][]" class="cp-input cp-video-url-input" placeholder="Paste a video URL (e.g. YouTube link)" value="{{ $videoUrlValue }}">
                           </div>
                        @endforeach
                     </div>
                     <small class="cp-file-hint">Paste a link to your video instead of uploading a file.</small>
                     @error('video_urls.'.$question->id)<small class="cp-error">{{ $message }}</small>@enderror

                     @if($question->allowed_multiples)
                        <button type="button" class="cp-add-more-file cp-add-more-video-url" data-question-id="{{ $question->id }}">
                           <i class="fas fa-plus"></i> Add another video URL
                        </button>
                     @endif
                  @endif
               </div>
            @endif
         </div>
      </div>
   @endforeach
@endif
