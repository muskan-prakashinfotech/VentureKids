@php
   $journeySteps = [
      ['color' => '#F2994A', 'icon' => 'fas fa-pencil-alt', 'title' => 'Create Draft'],
      ['color' => '#F2C94C', 'icon' => 'fas fa-lightbulb', 'title' => 'Get Improvement Ideas'],
      ['color' => '#27AE60', 'icon' => 'fas fa-rocket', 'title' => 'Improve Project'],
      ['color' => '#2F80ED', 'icon' => 'fas fa-paper-plane', 'title' => 'Submit for Review'],
      ['color' => '#9B51E0', 'icon' => 'fas fa-medal', 'title' => 'Teacher Approval'],
      ['color' => '#EB5288', 'icon' => 'fas fa-globe-americas', 'title' => 'Publish to Portfolio'],
   ];
@endphp

<div class="pi-card pi-journey">
   <div class="pi-steps">
      @foreach($journeySteps as $index => $jStep)
         @php
            $stepNum = $index + 1;
            $isDone = $stepNum < $activeStep;
            $isCurrent = $stepNum === $activeStep;
         @endphp
         <div class="pi-jstep {{ $isDone ? 'pi-jstep--done' : '' }} {{ $isCurrent ? 'pi-jstep--current' : '' }}" data-step="{{ $stepNum }}" data-color="{{ $jStep['color'] }}">
            <div class="pi-jstep-num" style="{{ $isDone || $isCurrent ? 'background:'.$jStep['color'].';' : '' }}">
               {{ $stepNum }}
            </div>
            <div class="pi-jstep-icon" style="{{ $isDone || $isCurrent ? 'color:'.$jStep['color'].';' : '' }}"><i class="{{ $jStep['icon'] }}"></i></div>
            <div class="pi-jstep-title">{{ $jStep['title'] }}</div>
         </div>
         @if(!$loop->last)
            <div class="pi-jstep-connector {{ $isDone ? 'pi-jstep-connector--done' : '' }}" data-connector-after="{{ $stepNum }}"></div>
         @endif
      @endforeach
   </div>
</div>
