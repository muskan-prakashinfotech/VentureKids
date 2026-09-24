@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">

   @php
      $stepItems = [
         ['num' => 1, 'color' => '#4F46E5', 'icon' => 'fas fa-feather-alt', 'title' => 'Create Draft', 'desc' => 'Write your idea and plan it out'],
         ['num' => 2, 'color' => '#14B8A6', 'icon' => 'fas fa-magic', 'title' => 'Get Improvement Ideas', 'desc' => 'Receive helpful tips from the system'],
         ['num' => 3, 'color' => '#2563EB', 'icon' => 'fas fa-tools', 'title' => 'Improve Project', 'desc' => 'Make your project even better'],
         ['num' => 4, 'color' => '#7C3AED', 'icon' => 'fas fa-cloud-upload-alt', 'title' => 'Submit for Review', 'desc' => 'Send to your teacher for feedback'],
         ['num' => 5, 'color' => '#0EA5E9', 'icon' => 'fas fa-check-circle', 'title' => 'Teacher Approval', 'desc' => 'Teacher reviews and approves your project'],
         ['num' => 6, 'color' => '#F59E0B', 'icon' => 'fas fa-trophy', 'title' => 'Publish Portfolio', 'desc' => 'Show your amazing project to the world!'],
      ];

      // student_projects has no title/theme column - those are just answered questions now,
      // not structured fields - so every card uses the same neutral illustration badge.
      $defaultThemeIcon = ['icon' => 'fas fa-lightbulb', 'bg' => '#EDEBFB'];

      // Status -> badge/button presentation, based on the student_projects 'status' column
      // (0/1 = draft, 2 = improvement ideas, 3 = improve project, 4 = submitted, 5 = approved, 6 = published).
      $resolveStatus = function ($project) {
         if ((int) $project->status === 6) {
            return [
               'label' => 'Published', 'badgeBg' => '#FCE4EC', 'badgeColor' => '#EB5288',
               'action' => 'View', 'actionColor' => '#EB5288',
               'route' => route('student.project-feedback', $project->id),
            ];
         }

         if ((int) $project->status === 5) {
            return [
               'label' => 'Approved', 'badgeBg' => '#F1E6FB', 'badgeColor' => '#9B51E0',
               'action' => 'View', 'actionColor' => '#9B51E0',
               'route' => route('student.project-feedback', $project->id),
            ];
         }

         if ((int) $project->status === 4) {
            $feedback = $project->feedback;

            if ($feedback && (int) $feedback->is_publish === 2) {
               return [
                  'label' => 'Approved', 'badgeBg' => '#F1E6FB', 'badgeColor' => '#9B51E0',
                  'action' => 'View', 'actionColor' => '#9B51E0',
                  'route' => route('student.confirm-submission', $project->id),
               ];
            }

            if ($feedback && (int) $feedback->is_publish === 1) {
               return [
                  'label' => 'Feedback Received', 'badgeBg' => '#E8F3FF', 'badgeColor' => '#2563EB',
                  'action' => 'View', 'actionColor' => '#27AE60',
                  'route' => route('student.confirm-submission', $project->id),
               ];
            }

            return [
               'label' => 'Submitted', 'badgeBg' => '#E1F3E8', 'badgeColor' => '#219653',
               'action' => 'View', 'actionColor' => '#27AE60',
               'route' => route('student.confirm-submission', $project->id),
            ];
         }

         if ((int) $project->status === 3) {
            return [
               'label' => 'Improving', 'badgeBg' => '#E1F8E9', 'badgeColor' => '#27AE60',
               'action' => 'Continue', 'actionColor' => '#27AE60',
               'route' => route('student.create-project', ['id' => $project->id]),
            ];
         }

         if ((int) $project->status === 2) {
            return [
               'label' => 'Improving', 'badgeBg' => '#E1EFFC', 'badgeColor' => '#2F80ED',
               'action' => 'View Suggestions', 'actionColor' => '#2D9CDB',
               'route' => route('student.project-suggestions', $project->id),
            ];
         }

         return [
            'label' => 'Draft', 'badgeBg' => '#FCEAD9', 'badgeColor' => '#E07A2C',
            'action' => 'Continue', 'actionColor' => '#F2994A',
            'route' => route('student.create-project', ['id' => $project->id]),
         ];
      };
   @endphp

   <div class="my-projects-page">

      @if(Session::has('message'))
         <div class="alert alert-success">
            {{ Session::get('message') }}
         </div>
      @endif

      <div class="mp-header">
         <h2>My Projects</h2>
         <p>Hi there, <strong class="mp-highlight">Young Innovator!</strong> Let's build something amazing today.</p>
      </div>

      <div class="mp-card mp-journey">
         <h3>Your Project Journey <span class="mp-journey-sub">(6 Simple Steps)</span></h3>
         <div class="mp-steps">
            @foreach($stepItems as $index => $step)
               <div class="mp-step">
                  <div class="mp-step-num" style="background:{{ $step['color'] }};">{{ $step['num'] }}</div>
                  <div class="mp-step-icon" style="color:{{ $step['color'] }};"><i class="{{ $step['icon'] }}"></i></div>
                  <div class="mp-step-title">{{ $step['title'] }}</div>
                  <div class="mp-step-desc">{{ $step['desc'] }}</div>
               </div>
               @if(!$loop->last)
                  <div class="mp-step-connector"></div>
               @endif
            @endforeach
         </div>
      </div>
      <div class="mp-body-grid">
         <div class="mp-main-col">
            <div class="mp-info-row mp-info-row--two-up">
               <a href="{{ route('student.create-project') }}" class="mp-info-card mp-info-card--orange mp-info-card--link">
                  <div class="mp-info-icon mp-info-icon--orange"><i class="fas fa-lightbulb"></i></div>
                  <div>
                     <strong>Start New Project</strong>
                     <p>Create your own idea and bring it to life!</p>
                  </div>
               </a>
               <div class="mp-info-card mp-info-card--blue">
                  <div class="mp-info-icon mp-info-icon--blue"><i class="fas fa-briefcase"></i></div>
                  <div>
                     <strong>Work on Assigned Project</strong>
                     <p>Complete the project your teacher gave you.</p>
                  </div>
               </div>
            </div>

            <div class="mp-projects-row">
               <h3><i class="fas fa-folder mp-projects-row-icon"></i> My Projects</h3>
            </div>
            <div class="mp-projects-grid">
               @forelse($projects as $project)
                  @php
                     $themeIcon = $defaultThemeIcon;
                     $status = $resolveStatus($project);
                  @endphp
                  <div class="mp-project-card">
                     <div class="mp-project-top">
                        <span class="mp-badge" style="background:{{ $status['badgeBg'] }}; color:{{ $status['badgeColor'] }};">{{ $status['label'] }}</span>
                        <button type="button" class="mp-project-menu" title="More options"><i class="fas fa-ellipsis-v"></i></button>
                     </div>

                     @if($project->display_image)
                        <div class="mp-project-image-wrap">
                           <img src="{{ $project->display_image->url }}" alt="" class="mp-project-image">
                        </div>
                     @else
                        <div class="mp-project-icon" style="background:{{ $themeIcon['bg'] }};">
                           <i class="{{ $themeIcon['icon'] }}"></i>
                        </div>
                     @endif

                     <div class="mp-project-title">{{ $project->display_title ?: 'Untitled Project' }}</div>
                     @if($project->display_theme)
                        <div class="mp-project-theme">Theme: <span>{{ $project->display_theme }}</span></div>
                     @endif
                     <div class="mp-project-edited"><i class="far fa-calendar-alt"></i> Last edited: {{ optional($project->updated_at)->format('M d, Y') }}</div>

                     <a href="{{ $status['route'] }}" class="mp-project-action" style="background:{{ $status['actionColor'] }};">
                        {{ $status['action'] }} <i class="fas fa-chevron-right"></i>
                     </a>
                  </div>
               @empty
                  <div class="mp-empty-state">
                     <i class="fas fa-lightbulb"></i>
                     <p>You haven't started a project yet.</p>
                     <a href="{{ route('student.create-project') }}" class="mp-btn-new">
                        <i class="fas fa-plus"></i> Start Your First Project
                     </a>
                  </div>
               @endforelse
            </div>
         </div>

      </div>
   </div>
</div>

@endsection
