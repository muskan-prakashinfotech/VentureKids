@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">

   @php
      $activeStep = 2; // Get Improvement Ideas

      $tipColors = ['#F2994A', '#F2C94C', '#27AE60', '#2F80ED', '#9B51E0', '#EB5288'];

      $readinessStyles = [
         'Not Ready Yet' => ['bg' => '#FDEBEB', 'color' => '#EB5757'],
         'Getting There' => ['bg' => '#FCEAD9', 'color' => '#E07A2C'],
         'Almost Ready' => ['bg' => '#E1EFFC', 'color' => '#2F80ED'],
         'Ready to Publish' => ['bg' => '#E1F3E8', 'color' => '#219653'],
         'Excellent Showcase' => ['bg' => '#F1E6FB', 'color' => '#9B51E0'],
      ];
      $readinessStyle = $readinessStyles[$readinessLevel] ?? ['bg' => '#F1F1F5', 'color' => '#6B6B80'];

      $hasParsedContent = $summary || $workingWell || $canImprove || $evidenceToAdd || count($tips);
      $wizardUrl = route('student.create-project', ['id' => $studentProject->id]);
   @endphp

   <div class="pi-page">

      <div class="pi-header">
         <a href="{{ route('student.my-projects') }}" class="pi-back"><i class="fas fa-arrow-left"></i></a>
         <div>
            <h2>Get Improvement Ideas</h2>
            <p>Our AI powered ImproveBuddy checks your project and gives you simple ways to make it better.</p>
         </div>
      </div>

      @if(Session::has('message'))
         <div class="alert alert-success">{{ Session::get('message') }}</div>
      @endif

      @include('student.project.partials.journey_stepper', ['activeStep' => $activeStep])

      <div class="pi-body-grid">
         <div class="pi-draft-col">
            <div class="pi-card pi-draft-card">
               <div class="pi-draft-top">
                  <span class="pi-draft-label"></span>
                  <span class="pi-badge pi-badge--draft">{{ $studentProject->status == 1 ? 'Improving' : 'Draft' }}</span>
               </div>

               @if($photos->isNotEmpty())
                  <div class="pi-draft-thumb">
                     <img src="{{ $photos->first()->url }}" alt="">
                  </div>
               @else
                  <div class="pi-draft-thumb pi-draft-thumb--placeholder">
                     <i class="fas fa-robot"></i>
                  </div>
               @endif

               <div class="pi-draft-title">{{ $displayTitle ?: 'Untitled Project' }}</div>
               @if($displayTheme)
                  <div class="pi-draft-theme">Theme: <span>{{ $displayTheme }}</span></div>
               @endif

               @if($photos->isNotEmpty())
                  <div class="pi-draft-photos">
                     <div class="pi-draft-photos-label">Uploaded Photos</div>
                     <div class="pi-photo-grid">
                        @foreach($photos->shuffle()->take(4) as $photo)
                           <a href="{{ route('student.download-attachment', $photo->id) }}" class="pi-photo-thumb">
                              <img src="{{ $photo->url }}" alt="">
                           </a>
                        @endforeach
                     </div>
                  </div>
               @endif
            </div>
         </div>

         <div class="pi-main-col">
            <div class="pi-card pi-ai-card">
               <div class="pi-ai-header">
                  <div class="pi-ai-avatar"><i class="fas fa-robot"></i></div>
                  <div class="pi-ai-greeting">
                     <h3>Hi! I am your AI powered ImproveBuddy <i class="fas fa-crown"></i></h3>
                     <p>I looked at your project and found some simple ways to make it even better and stronger.</p>
                  </div>
                  @if(count($tips))
                     <div class="pi-tip-count"><i class="fas fa-star"></i> {{ count($tips) }} tips found</div>
                  @endif
               </div>

               @if($hasParsedContent)
                  <div class="pi-section">
                     <h4>Basic Details</h4>
                     @if($summary)
                        <div class="pi-subsection">
                           <strong>Summary</strong>
                           <p>{{ $summary }}</p>
                        </div>
                     @endif
                     @if($workingWell)
                        <div class="pi-subsection">
                           <strong>What's Working Well</strong>
                           <p>{{ $workingWell }}</p>
                        </div>
                     @endif
                     @if($canImprove)
                        <div class="pi-subsection">
                           <strong>What Can Be Improved</strong>
                           <p>{{ $canImprove }}</p>
                        </div>
                     @endif
                     @if($evidenceToAdd)
                        <div class="pi-subsection">
                           <strong>Evidence to Add</strong>
                           <p>{!! nl2br(e($evidenceToAdd)) !!}</p>
                        </div>
                     @endif
                  </div>

                  @if(count($tips))
                     <div class="pi-section">
                        <h4>Improvement Tips</h4>
                        <div class="pi-tip-list">
                           @foreach($tips as $index => $tip)
                              @php
                                 $color = $tipColors[$index % count($tipColors)];
                              @endphp
                              <div class="pi-tip-row">
                                 <div class="pi-tip-num" style="background:{{ $color }};">{{ $index + 1 }}</div>
                                 <div class="pi-tip-body">
                                    <div class="pi-tip-title">{{ $tip['action'] }}</div>
                                    @if($tip['why'])
                                       <div class="pi-tip-desc">{{ $tip['why'] }}</div>
                                    @endif
                                 </div>
                              </div>
                           @endforeach
                        </div>
                     </div>
                  @endif
               @else
                  <div class="pi-section">
                     <div class="pi-raw-report">{{ $rawReport }}</div>
                  </div>
               @endif

               @if($readinessLevel)
                  <div class="pi-section pi-readiness">
                     <div class="pi-readiness-list">
                        @foreach($readinessStyles as $level => $style)
                           <span
                              class="pi-readiness-pill {{ $level === $readinessLevel ? 'pi-readiness-pill--active' : 'pi-readiness-pill--inactive' }}"
                              style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};"
                              @if($level === $readinessLevel) aria-current="step" @endif
                           >
                              {{ $level }}
                           </span>
                        @endforeach
                     </div>
                     @if($readinessExplanation)
                        <p>{{ $readinessExplanation }}</p>
                     @endif
                  </div>
               @endif
            </div>

            <div class="pi-bottom-bar">
               <a href="{{ route('student.my-projects') }}" class="pi-btn-secondary"><i class="fas fa-save"></i> Save</a>
               <a href="{{ $wizardUrl }}" class="pi-btn-primary">Go Improve My Project <i class="fas fa-chevron-right"></i></a>
            </div>
         </div>
      </div>

   </div>
</div>

@endsection
