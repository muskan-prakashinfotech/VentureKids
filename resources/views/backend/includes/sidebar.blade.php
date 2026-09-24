<!-- Main Sidebar Container -->
<aside class="main-sidebar NewVersionSidebar orange-sidebar ">
   <!-- Brand Logo -->
   @switch(auth()->user()->group)
   @case(1)
   @case(5)
   <a href="{{ route('backend.dashboard') }}" class="brand-link">
   <img src="{{ asset_v('asset/images/logo-icon.png') }}" alt="AdminLTE Logo" class="brand-image img-circle"
      style="height: 36px; width: 36px; opacity: .8;">
   <span class="brand-text font-weight-light">VentureKids</span>
   </a>
   <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="pb-3 mt-3 mb-3 user-panel d-flex">
         <div class="image">
            @if (Session::get('admin_image'))
            <img src="{{ asset_v(Session::get('admin_image')) }}" class="img-circle elevation-2" alt=""
               style="height: 36px; width: 36px;">
            @endif
         </div>
         <div class="info">
            <a href="{{ route('backend.dashboard') }}" class="d-block user-type-name">{{ Session::get('admin_name') }}</a>
         </div>
      </div>
      <!-- Sidebar Menu -->
      <nav class="mt-2 NewVersionUpdateMenus">
         <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
            data-accordion="false">
            <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
            <li class="nav-item menu-open">
               <a href="{{ route('backend.dashboard') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'backend.dashboard'? 'active': '' }}
                  ">
                  <i class="nav-icon ion ion-stats-bars"></i>
                  <p>
                     Dashboard
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-school"></i>
                  <p>
                     School Onboarding
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.schoollist.schoolList') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.schoollist.schoolList'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>School List</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.schoolcreate.schoolCreate') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.schoolcreate.schoolCreate'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Create School </p>
                     </a>
                  </li>
                  @if (!isPartnerUser())
                  <li class="nav-item">
                     <a href="{{ route('backend.school-notificationbox') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.school-notificationbox'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Notification Message </p>
                     </a>
                  </li>
                  @endif
                  @if (isSuperAdminUser())
                  <li class="nav-item">
                     <a href="{{ route('backend.pending-schools.index') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.pending-schools.index'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Pending School Approvals</p>
                     </a>
                  </li>
                  @endif
               </ul>
            </li>
            @if (auth()->user()->group == 1)
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-user-shield"></i>
                  <p>
                     Manage Partners
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.partners.index') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.partners.index'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Partner List</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.partners.create') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.partners.create'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add Partner</p>
                     </a>
                  </li>
               </ul>
            </li>
            @endif
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-chalkboard-teacher"></i>
                  <p>
                     Trainer Onboarding
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.trainerlist.trainerList') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.trainerlist.trainerList'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Trainer List</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.addtrainer.addTrainer') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.addtrainer.addTrainer'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add Trainer </p>
                     </a>
                  </li>
                  {{--
                  <li class="nav-item">
                     <a href="{{ route('backend.trainer-notificationbox') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.trainer-notificationbox'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Notification Message </p>
                     </a>
                  </li>
                  --}}
               </ul>
            </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-chalkboard-teacher"></i>
                  <p>
                     Trainer Allocation
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.trainerallocation.trainerallocation') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.trainerallocation.trainerallocation'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Assign Trainer</p>
                     </a>
                  </li>
               </ul>
            </li>
            @if (!isPartnerUser())
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-book"></i>
                  <p>
                     Content
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.addcontent.addContent') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.addcontent.addContent'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add Content Trainers</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.contentlist.contentList') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.contentlist.contentList'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Content List Trainers</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.addcontent.addContentStudents') }}"
                     @class([
                     'nav-link',
                     'active' => Route::is('backend.addcontent.addContentStudents'),
                     ])>
                     <i class="far fa-circle nav-icon"></i>
                     <p>Add Content Students</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.addcontent.contentListStudents') }}"
                     @class([
                     'nav-link',
                     'active' => Route::is('backend.addcontent.contentListStudents'),
                     ])>
                     <i class="far fa-circle nav-icon"></i>
                     <p>Content List Students</p>
                     </a>
                  </li>
                  {{--
                  <li class="nav-item">
                     <a href="{{ route('backend.addcontent.contentAddStudent') }}"
                     @class([
                     'nav-link',
                     Route::is('backend.addcontent.contentAddStudent') => 'active',
                     ])>
                     <i class="far fa-circle nav-icon"></i>
                     <p>Add Content Students</p>
                     </a>
                  </li>
                  --}}
               </ul>
            </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-list"></i>
                  <p>
                     Assignment
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.assignmentlist') }}" @class(['nav-link', 'active' => Route::is('backend.assignmentlist')])>
                     <i class="far fa-circle nav-icon"></i>
                     <p class="ml-2">Assignment List</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.create-assignment') }}" class="nav-link
                     {{ request()->route()->getName() == 'backend.create-assignment'? 'active': '' }}">
                     <i class="far fa-circle nav-icon"></i>
                     <p class="ml-2">Allocate Assignment</p>
                     </a>
                  </li>
               </ul>
            </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="fas fa-upload"></i>
                  <p>
                     Level
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.level.index') }}" @class(['nav-link', 'active' => Route::is('backend.level.index')])>
                     <i class="far fa-circle nav-icon"></i>
                     <p class="ml-2">Student Level</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.trainerlevel.index') }}" @class(['nav-link', 'active' => Route::is('backend.trainerlevel.index')])>
                     <i class="far fa-circle nav-icon"></i>
                     <p class="ml-2">Trainer Level</p>
                     </a>
                  </li>
               </ul>
            </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="fas fa-upload"></i>
                  <p>
                     Stream
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.stream.index') }}" @class(['nav-link', 'active' => Route::is('backend.stream.index')])>
                     <i class="far fa-circle nav-icon"></i>
                     <p class="ml-2">Student Stream</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.trainerstream.index') }}" @class(['nav-link', 'active' => Route::is('backend.trainerstream.index')])>
                     <i class="far fa-circle nav-icon"></i>
                     <p class="ml-2">Trainer Stream</p>
                     </a>
                  </li>
               </ul>
            </li>
            <li class="nav-item">
           <a href="{{ route('backend.student.playaffirmation') }}"
               class="nav-link  {{ request()->route()->getName() == 'backend.student.playaffirmation'? 'active': '' }}">
              <i class="nav-icon fas fa-music"></i></i>
               <p>Play Affirmation</p>
            </a>
         </li>
            @endif
            {{--
            <li class="nav-item">
               <a href="{{ route('backend.level.index') }}" @class(['nav-link', 'active' => Route::is('backend.level.index')])>
               <i class="fas fa-upload"></i>
               <p class="ml-2">Level List</p>
               </a>
            </li>
            --}}

            <li class="nav-item">
         <a href="{{ route('backend.student.loginhistory') }}"
          class="nav-link  {{ request()->route()->getName() == 'backend.student.loginhistory'? 'active': '' }}">
         <i class="nav-icon fas fa-sign-in-alt"></i>
         <p>Login History</p>
         </a>
         </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-list"></i>
                  <p>
                     Challenges
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.eventlist.eventlist') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.eventlist.eventlist'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Industry Challenges</p>
                     </a>
                  </li>
                  @if (!isPartnerUser())
                  <li class="nav-item">
                     <a href="{{ route('backend.weeklyChallengeList') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.weeklyChallengeList'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Daily Quiz</p>
                     </a>
                  </li>
                  @endif
               </ul>
            </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-list"></i>
                  <p>
                     Observations
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  @if (!isPartnerUser())
                  <li class="nav-item">
                     <a href="{{ route('backend.observation_list') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.observation_list'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Observations List</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.observation.create') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.observation.create'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add Observation</p>
                     </a>
                  </li>
                  @endif
                  <li class="nav-item">
                     <a href="{{ route('backend.observations.all') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.observations.all'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>View All Observation</p>
                     </a>
                  </li>
               </ul>
            </li>
            <!-- <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-list"></i>
                  <p>
                     Mindset
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.mindset_list') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.mindset_list'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mindset List</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.mindset.create') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.mindset.create'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add Mindset</p>
                     </a>
                  </li>
               </ul>
            </li> -->
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-list"></i>
                  <p>
                     Session
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.external_session.list') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.external_session.list'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Session List</p>
                     </a>
                  </li>
            <li class="nav-item">
               <a href="{{ route('backend.external_session.create') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.external_session.create'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add Session</p>
                     </a>
                  </li>
               </ul>
            </li>
            @if (!isPartnerUser())
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-list"></i>
                  <p>
                     AI Tools
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                   <li class="nav-item">
                     <a href="{{ route('backend.aiToollist.aiToolList') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.aiToollist.aiToolList'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>AI Tool List</p>
                     </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('backend.aiToolcreate.aiToolCreate') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.aiToolcreate.aiToolCreate'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add AI Tool</p>
                     </a>
                  </li>
               </ul>
            </li>
            <li class="nav-item">
               <a href="{{ route('backend.projectmanagement.index') }}"
                  @class(['nav-link', 'active' => Route::is('backend.projectmanagement.*', 'backend.projectSection*', 'backend.projectQuestion*', 'backend.getProjectSection', 'backend.getProjectQuestion')])>
                  <i class="nav-icon fas fa-list"></i>
                  <p>Project Management</p>
               </a>
            </li>
            @endif
            <li class="nav-item">
               <a href="{{ route('backend.realqassessment.index') }}"
                  @class(['nav-link', 'active' => Route::is('backend.realqassessment.*')])>
                  <i class="nav-icon fas fa-clipboard-list"></i>
                  <p>RealQ Assessment</p>
               </a>
            </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-clipboard-list"></i>
                  <p>
                     Standard Assessment
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  @if (!isPartnerUser())
                  <li class="nav-item">
                     <a href="{{ route('backend.standard_assessment.categories.list') }}"
                        class="nav-link {{ Route::is('backend.standard_assessment.categories.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Categories</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.standard_assessment.questions.list') }}"
                        class="nav-link {{ Route::is('backend.standard_assessment.questions.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Questionnaire</p>
                     </a>
                  </li>
                  @endif
                  <li class="nav-item">
                     <a href="{{ route('backend.standard_assessment.export.index') }}"
                        class="nav-link {{ Route::is('backend.standard_assessment.export.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Export</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('backend.standard_assessment.attempts.list') }}"
                        class="nav-link {{ Route::is('backend.standard_assessment.attempts.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Attempt Tracking</p>
                     </a>
                  </li>
               </ul>
            </li>
            @if (!isPartnerUser())
            <li class="nav-item">
               <a href="{{ route('backend.createvideo.createVideo') }}"
               class="nav-link  {{ request()->route()->getName() == 'backend.createvideo.createVideo'? 'active': '' }}">
               <i class="nav-icon fas fa-list"></i></i>
               <p>How It Works</p>
            </a>
            </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-list"></i>
                  <p>
                     Others
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('backend.resources.resources') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'backend.resources.resources'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Other Resources </p>
                     </a>
                  </li>
               </ul>
            </li>
            @endif
            <!-- <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-file-download"></i>
                  <p>
                     Export
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="#"
                        class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Observation Data</p>
                     </a>
                  </li>
               </ul>
            </li> -->
            <li class="nav-item">
               <a href="{{ route('logout') }}" class="nav-link"
                  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <form id="logout-form" action="{{ route('logout') }}" method="POST"
                     style="display: none;">
                     @csrf
                  </form>
                  <i class="nav-icon fas fa-lock"></i>
                  <p>
                     Logout
                  </p>
               </a>
            </li>
         </ul>
      </nav>
      </ul>
      </nav>
      <!-- /.sidebar-menu -->
   </div>
   @break
   @case(2)
      @php
         $showSchoolStandardAssessmentMenu = false;
         $schoolStandardData = \App\Models\School::select('standard_assessment_assigned')->find(Session::get('school_id'));
         if ($schoolStandardData && (int) $schoolStandardData->standard_assessment_assigned === 1) {
            $showSchoolStandardAssessmentMenu = true;
         }

         $showSchoolRealQAssessmentMenu = false;
         $schoolRealQData = \App\Models\RealQAssessmentSchoolAssignment::where('school_id', Session::get('school_id'))
            ->where('realq_assessment_assigned', 1)
            ->exists();
         if ($schoolRealQData) {
            $showSchoolRealQAssessmentMenu = true;
         }
      @endphp
   @php $schoolBrand = school_logo_cover_name(); @endphp
   <a href="{{ route('school.dashboard') }}" class="brand-link">
   <img src="{{ empty($schoolBrand['schoolSidebarLogoPath']) ? $schoolBrand['schoolLogoPath'] : $schoolBrand['schoolSidebarLogoPath'] }}" alt="AdminLTE Logo"
      class="brand-image img-circle" style="height: 36px; width: 36px; opacity: .8;">
   <span class="brand-text font-weight-light" style="line-height: 1.15;">
      <span style="display: block; font-weight: 600;">VentureKids</span>
      @if($schoolBrand['schoolName'] !== 'VentureKids')
      <span style="display: block; font-size: 11px; font-weight: 400; opacity: .85;">{{ $schoolBrand['schoolName'] }}</span>
      @endif
   </span>
   </a>
   <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <!-- <div class="pb-3 mt-3 mb-3 user-panel d-flex">
         <div class="image">
            @if (Session::get('school_image'))
            <img src="{{ asset_v(Session::get('school_image')) }}" class="img-circle elevation-2"
               alt="">
            @endif
         </div>
         <div class="info">
            <a href="{{ route('school.dashboard') }}"
               class="d-block text-wrap">{{ Session::get('school_name') }}</a>
         </div>
      </div> -->
      <!-- Sidebar Menu -->
      <nav class="mt-2 NewVersionUpdateMenus">
         <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
            data-accordion="false">
            <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
            <li class="nav-item">
               <a href="{{ route('school.dashboard') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'school.dashboard'? 'active': '' }}
                  ">
                  <i class="nav-icon ion ion-stats-bars"></i>
                  <p>
                     Dashboard
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('school.profile-edit') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'school.profile-edit'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-address-card"></i>
                  <p>
                     Profile
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('school.student-list') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'school.student-list'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-user-graduate"></i>
                  <p>
                     Student Management
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('school.batch-list') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'school.batch-list'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-user-graduate"></i>
                  <p>
                     School Batch Management
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('school.Class_schedule') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'school.Class_schedule'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-user-graduate"></i>
                  <p>
                     Class Schedule
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('school.progress-report') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'school.progress-report'? 'active': '' }}
                  ">
                  <i class="fas fa-chart-line nav-icon"></i>
                  <p>Student Progress Report</p>
               </a>
            </li>
            <!-- <li class="nav-item">
               <a href="#"
                  class="nav-link">
                  <i class="nav-icon fas fa-file-download"></i>
                  <p>Export Observation Data</p>
               </a>
            </li> -->
            @if($showSchoolStandardAssessmentMenu)
            <li class="nav-item">
               <a href="{{ route('school.standard-assessment.index') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'school.standard-assessment.index'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-clipboard-list"></i>
                  <p>Standard Assessment</p>
               </a>
            </li>
            @endif
            @if($showSchoolRealQAssessmentMenu)
            <li class="nav-item">
               <a href="{{ route('school.realq-assessment.index') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'school.realq-assessment.index'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-clipboard-list"></i>
                  <p>RealQ Assessment</p>
               </a>
            </li>
            @endif
            {{--
            <li class="nav-item">
               <a href="{{ route('school.event-list') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'school.event-list'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-calendar-alt"></i>
                  <p>
                     Challenges
                  </p>
               </a>
            </li>
            --}}
            <li class="nav-item">
               <a href="{{ route('school.privacy-police') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'school.privacy-police'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p>
                     Terms & Privacy Policy
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('logout') }}" class="nav-link"
                  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <form id="logout-form" action="{{ route('logout') }}" method="POST"
                     style="display: none;">
                     @csrf
                  </form>
                  <i class="nav-icon fas fa-lock"></i>
                  <p>
                     Logout
                  </p>
               </a>
            </li>
         </ul>
      </nav>
      </ul>
      </nav>
      <!-- /.sidebar-menu -->
   </div>
   @break
   @case(3)
   <a href="{{ route('trainer.dashboard') }}" class="brand-link">
   <img src="{{ asset_v('asset/images/logo-icon.png') }}" alt="AdminLTE Logo"
      class="brand-image img-circle" style="height: 36px; width: 36px; opacity: .8;">
   <span class="brand-text font-weight-light">VentureKids</span>
   </a>
   <div class="sidebar">
      <!-- Sidebar user panel (optional) -->

      <!-- Sidebar Menu -->
      <nav class="mt-2 NewVersionUpdateMenus">
         <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
            data-accordion="false">
            <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
            <li class="nav-item">
               <a href="{{ route('trainer.dashboard') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'trainer.dashboard'? 'active': '' }}
                  ">
                  <i class="nav-icon ion ion-stats-bars"></i>
                  <p>
                     Dashboard
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('trainer.profile') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'trainer.profile'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-address-card"></i>
                  <p>
                     Profile
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('trainer.content/list.contentList') }}" @class([
               'nav-link',
               'active' =>
               Route::is('trainer.contentlist.streamlist') ||
               Route::is('trainer.contentview.contentView') ||
               Route::is('trainer.content/list.contentList'),
               ])>
               <i class="nav-icon fas fa-photo-video"></i>
               <p>
                  Content
               </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('trainer.Class_schedule') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'trainer.Class_schedule'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-chalkboard-teacher"></i>
                  <p>
                     Class Schedule
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-user-graduate"></i>
                  <p>
                     Students
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('trainer.student_list') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'trainer.student_list'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Students Progress</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('trainer.student_leaderboard') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'trainer.student_leaderboard'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Leaderboard</p>
                     </a>
                  </li>
                  <!-- <li class="nav-item">
                     <a href="{{ route('trainer.student_attendence') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'trainer.student_attendence'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Attendance </p>
                     </a>
                  </li> -->
               </ul>
            </li>
            {{--
            <li class="nav-item">
               <a href="{{ route('trainer.event/list.eventList') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'trainer.event/list.eventList'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-calendar-alt"></i>
                  <p>
                     Challenges
                  </p>
               </a>
            </li>
            --}}
            <li class="nav-item">
               <a href="{{ route('trainer.list-project') }}" @class([
               'nav-link',
               'active' => Route::is('trainer.list-project')])>
                  <i class="nav-icon nav-icon fas fa-lightbulb"></i>
                  <p>
                     Projects
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('trainer.todo_index') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'trainer.todo_index'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-clipboard-check"></i>
                  <p>
                     To Do
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-clock"></i>
                  <p>
                     Assignment
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('trainer.createAssignment') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'trainer.createAssignment'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Create Assignment </p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('trainer.assigment.index') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'trainer.assigment.index'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Assignment View </p>
                     </a>
                  </li>
               </ul>
            </li>
            <li class="nav-item">
               <a href="{{ route('trainer.trainer-certificate') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'trainer.trainer-certificate'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-certificate"></i>
                  <p>
                     Certification
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('trainer.certificates.management') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'trainer.certificates.management'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-graduation-cap"></i>
                  <p>
                     Student Certificates
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('trainer.termsandprivacypolicy') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'trainer.termsandprivacypolicy'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p>
                     Terms & Privacy Policy
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('logout') }}" class="nav-link"
                  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <form id="logout-form" action="{{ route('logout') }}" method="POST"
                     style="display: none;">
                     @csrf
                  </form>
                  <i class="nav-icon fas fa-lock"></i>
                  <p>
                     Logout
                  </p>
               </a>
            </li>
         </ul>
      </nav>
      </ul>
      </nav>
      <!-- /.sidebar-menu -->
   </div>
   @break
   @case(4)
      @if(tenant())
        @php $schoolBrand = school_logo_cover_name(); @endphp
        <a href="{{ route('student.dashboard') }}" class="brand-link">
             <img src="{{ empty($schoolBrand['schoolSidebarLogoPath']) ? $schoolBrand['schoolLogoPath'] : $schoolBrand['schoolSidebarLogoPath'] }}" alt="AdminLTE Logo"
                class="brand-image img-circle" style="height: 36px; width: 36px; opacity: .8;">
             <span class="brand-text font-weight-light" style="line-height: 1.15;">
                <span style="display: block; font-weight: 600;">VentureKids</span>
                @if($schoolBrand['schoolName'] !== 'VentureKids')
                <span style="display: block; font-size: 11px; font-weight: 400; opacity: .85;">{{ $schoolBrand['schoolName'] }}</span>
                @endif
             </span>
        </a>
      @else
        <a href="{{ route('student.dashboard') }}" class="brand-link">
             <img src="{{ asset_v('asset/images/logo-icon.png') }}" alt="AdminLTE Logo"
                class="brand-image img-circle" style="height: 36px; width: 36px; opacity: .8;">
             <span class="brand-text font-weight-light">VentureKids</span>
        </a>
      @endif
   </a>
   <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <!-- <div class="pb-3 mt-3 mb-3 user-panel d-flex">
         <div class="image">
            @if (Session::get('student_image') != 'no_image')
            <img class="profile-user-img img-fluid img-circle"
               src="{{ asset_v(Session::get('student_image')) }}" alt="User profile picture">
            @else
            <img src="{{ asset('img/default-150x150.png') }}" alt="Product 1" class="mr-2 img-circle">
            @endif
         </div>
         <div class="info">
            <a href="{{ route('student.dashboard') }}" class="d-block">{{ Session::get('student_name') }}</a>
         </div>
      </div> -->
      <!-- Sidebar Menu -->
      <nav class="mt-2 NewVersionUpdateMenus">
         <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
            data-accordion="false">
            <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
            <li class="nav-item">
               <a href="{{ route('student.dashboard') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'student.dashboard'? 'active': '' }}
                  ">
                  <i class="nav-icon ion ion-stats-bars"></i>
                  <p>
                     Dashboard
                  </p>
               </a>
            </li>
            {{--
            <li class="nav-item">
               <a href="{{ route('student.student-profile') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'student.student-profile'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-address-card"></i>
                  <p>
                     Profile
                  </p>
               </a>
            </li>
            --}}
            <li class="nav-item">
               <a href="{{ route('student.contentlist.contentList') }}" @class([
               'nav-link',
               'active' =>
               Route::is('student.contentlist.streamlist') ||
               Route::is('student.contentview.contentView') ||
               Route::is('student.contentlist.contentList'),
               ])>
               <i class="nav-icon fas fa-photo-video"></i>
               <p>
                  Content
               </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('student.assignment') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'student.assignment'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-calendar-alt"></i>
                  <p>
                     Assignment
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('student.Class_schedule') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'student.Class_schedule'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-user-graduate"></i>
                  <p>
                     Class Schedule
                  </p>
               </a>
            </li>
             <li class="nav-item">
               <a href="{{ route('student.my-projects') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'student.my-projects'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-lightbulb"></i>
                  <p>
                     My Project
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('student.marketplace') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'student.marketplace'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-store"></i>
                  <p>
                     My Marketplace
                  </p>
               </a>
            </li>
            <!-- <li class="nav-item">
               <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-lightbulb"></i>
                  <p>
                     My Project
                     <i class="fas fa-angle-left right"></i>
                  </p>
               </a>
               <ul class="nav nav-treeview">
                  <li class="nav-item">
                     <a href="{{ route('student.list-project') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'student.list-project'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>All Projects</p>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('student.add-project') }}"
                        class="nav-link
                        {{ request()->route()->getName() == 'student.add-project'? 'active': '' }}
                        ">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add Project</p>
                     </a>
                  </li>
               </ul>
            </li> -->
            
            <li class="nav-item">
               <a href="{{ route('student.event_list') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'student.event_list'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-calendar-alt"></i>
                  <p>
                     Challenges
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('student.my-workspace') }}" class="nav-link   
               {{ request()->route()->getName() == 'student.my-workspace'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-briefcase"></i>
                  <p>
                     My Workspace
                  </p>
               </a>
            </li>
             <li class="nav-item">
               <a href="{{ route('student.assessment') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'student.assessment'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-calendar-alt"></i>
                  <p>
                     Assessment
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('student.student-certificate') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'student.student-certificate'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-certificate"></i>
                  <p>
                     Certification
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('student.term_and_privacy_policy') }}"
                  class="nav-link
                  {{ request()->route()->getName() == 'student.term_and_privacy_policy'? 'active': '' }}
                  ">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p>
                     Terms & Privacy Policy
                  </p>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('logout') }}" class="nav-link"
                  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <form id="logout-form" action="{{ route('logout') }}" method="POST"
                     style="display: none;">
                     @csrf
                  </form>
                  <i class="nav-icon fas fa-lock"></i>
                  <p>
                     Logout
                  </p>
               </a>
            </li>
         </ul>
      </nav>


      </ul>
      </nav>
      <!-- <div class="SidebarBottom">
         <div><span>Version 1.1</span></div>
            <div><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></div>
      </div> -->
      <!-- /.sidebar-menu -->
   </div>
   @break
   @default
   @endswitch
   <!-- Sidebar -->
</aside>


