{{-- Begin Project --}}
@foreach($paginator->items() as $project)
<div class="col-md-6 col-xl-4">
    <!--begin::Card-->
    <a href="{{ route('project.edit', ['project_id' => $project->id ]) }}" class="card border-hover-primary">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-9">
            <!--begin::Card Title-->
            <div class="card-title m-0">
                <!--begin::Avatar-->
                <div class="symbol symbol-50px w-50px bg-light">
                    @if($project->project_logo)
                    <img src="/metronic8/demo1/assets/media/svg/brand-logos/plurk.svg" alt="image" class="p-3">
                    @else
                    <span class="symbol-label {{ $project->background }} text-inverse-primary fw-bold">{{ $project->first_letter_name }}</span>
                    @endif
                </div>
                <!--end::Avatar-->
            </div>
            <!--end::Car Title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <span class="badge {{ $project->badge_status }} fw-bold me-auto px-4 py-3">@lang('admin.projects.'.$project->project_status)</span>
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end:: Card header-->
        <!--begin:: Card body-->
        <div class="card-body p-9">
            <!--begin::Name-->
            <div class="fs-3 fw-bold text-dark">{{ $project->project_name }}</div>
            <!--end::Name-->
            <!--begin::Description-->
            <p class="text-gray-400 fw-semibold fs-5 mt-1 mb-7">{{ $project->project_description }}</p>
            <!--end::Description-->
            <!--begin::Info-->
            <div class="d-flex flex-wrap mb-5">
                <!--begin::Due-->
                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-7 mb-3">
                    <div class="fs-6 text-gray-800 fw-bold">{{ $project->project_due_date }}</div>
                    <div class="fw-semibold text-gray-400">@lang('admin.projects.project_due_date')</div>
                </div>
                <!--end::Due-->
                <!--begin::Budget-->
                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 mb-3">
                    <div class="fs-6 text-gray-800 fw-bold">${{ $project->project_budget }}</div>
                    <div class="fw-semibold text-gray-400">@lang('admin.projects.budget')</div>
                </div>
                <!--end::Budget-->
            </div>
            <!--end::Info-->
            <!--begin::Progress-->
            <div class="h-4px w-100 bg-light mb-5" data-bs-toggle="tooltip" aria-label="This project 50% completed"
                data-bs-original-title="This project 50% completed" data-kt-initialized="1">
                <div class="bg-primary rounded h-4px" role="progressbar" style="width: 50%" aria-valuenow="50"
                    aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <!--end::Progress-->
            <!--begin::Users-->
            <div class="symbol-group symbol-hover">
                @if(!empty($project->project_users))


                @foreach($project->project_users as $pu)
                <!--begin::User-->
                <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="Emma Smith"
                    data-bs-original-title="Emma Smith" data-kt-initialized="1">
                    @if(isset($pu->users->avatar))
                    <img alt="Pic" src="{{ asset($pu->users->avatar) }}">
                    @else
                    <span class="symbol-label {{ $pu->users->background }} text-inverse-primary fw-bold">{{ $pu->users->first_letter_name }}</span>
                    @endif
                </div>
                 <!--end::User-->
                @endforeach
                @endempty
            </div>
            <!--end::Users-->
        </div>
        <!--end:: Card body-->
    </a>
    <!--end::Card-->
</div>
@endforeach




