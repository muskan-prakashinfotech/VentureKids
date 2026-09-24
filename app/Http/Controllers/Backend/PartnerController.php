<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\CreatedPartnerMail;
use App\Jobs\PartnerPartnershipReminder;
use App\Models\Country;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laracasts\Flash\Flash;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    public function index()
    {
        return view('backend.partners.index', [
            'title' => 'Manage Partners',
            'module_title' => 'Partners',
            'module_action' => 'List',
            'module_icon' => 'fas fa-user-shield',
            'module_name' => 'partners',
        ]);
    }

    public function index_data()
    {
        $partners = User::query()
            ->select([
                'id',
                'name',
                'email',
                'username',
                'mobile',
                'status',
                'allow_add_trainers',
                'no_of_license_purchased',
                'updated_at',
            ])
            ->where('group', 5)
            ->whereNull('deleted_at');

        return DataTables::of($partners)
            ->editColumn('name', function ($data) {
                return e($data->name);
            })
            ->editColumn('email', function ($data) {
                return e($data->email);
            })
            ->editColumn('username', function ($data) {
                return e($data->username);
            })
            ->editColumn('mobile', function ($data) {
                return e($data->mobile);
            })
            ->addColumn('license_purchase', function ($data) {
                return (int) $data->no_of_license_purchased;
            })
            ->addColumn('trainers_allowed', function ($data) {
                return (int) $data->allow_add_trainers;
            })
            ->editColumn('status', function ($data) {
                return $data->status_label;
            })
            ->addColumn('action', function ($data) {
                return '
                    <a href="'.route('backend.partners.edit', $data->id).'" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="'.route('backend.partners.destroy', $data->id).'" method="POST" class="d-inline">
                        '.csrf_field().'
                        '.method_field('DELETE').'
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete this partner?\')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('backend.partners.create', [
            'countries' => Country::orderBy('name')->get(['id', 'name']),
            'currencies' => DB::table('currencies')->orderBy('code')->get(['id', 'code']),
            'title' => 'Add Partner',
            'module_title' => 'Partners',
            'module_action' => 'Create',
            'module_icon' => 'fas fa-user-shield',
            'module_name' => 'partners',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:191',
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'username' => ['required', 'string', 'max:191', 'unique:users,username'],
            'mobile' => 'required|string|max:30',
            'country' => 'required|exists:countrys,id',
            'password' => 'nullable|string|min:6|max:191',
            'allow_add_trainers' => 'required|integer|min:0',
            'no_of_license_purchased' => 'required|integer|min:0',
            'partnership_start_date' => 'required|date_format:Y-m-d',
            'partnership_end_date' => 'required|date_format:Y-m-d|after_or_equal:partnership_start_date',
            'currency_id' => 'required|integer|exists:currencies,id',
        ]);

        $password = !empty($validated['password']) ? $validated['password'] : Str::random(10);

        $partner = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'mobile' => $validated['mobile'] ?? null,
            'country_id' => $validated['country'],
            'password' => Hash::make($password),
            'group' => 5,
            'allow_add_trainers' => $validated['allow_add_trainers'] ?? 0,
            'no_of_license_purchased' => $validated['no_of_license_purchased'],
            'partnership_start_date' => $validated['partnership_start_date'],
            'partnership_end_date' => $validated['partnership_end_date'],
            'currency_id' => $validated['currency_id'],
            'status' => 1,
            'suspend' => 2,
            'avatar' => 'img/default_image.png'
        ]);

        Log::info('Partner created', [
            'partner_id' => $partner->id,
            'created_by' => auth()->id(),
        ]);

        safeMailAction('partner onboarding mail', [
            'partner_id' => $partner->id,
            'recipient' => $partner->email,
        ], function () use ($partner, $password) {
            Mail::to($partner->email)->bcc(env('MAIL_BCC'))->send(
                new CreatedPartnerMail($partner->name, $partner->username, $password, $partner->no_of_license_purchased)
            );
        });

        $this->schedulePartnershipReminders($partner);

        Flash::success("Partner created successfully. Temporary password: {$password}")->important();

        return redirect()->route('backend.partners.index');
    }

    public function edit($id)
    {
        $partner = User::where('group', 5)->findOrFail($id);

        return view('backend.partners.edit', [
            'partner' => $partner,
            'countries' => Country::orderBy('name')->get(['id', 'name']),
            'currencies' => DB::table('currencies')->orderBy('code')->get(['id', 'code']),
            'title' => 'Edit Partner',
            'module_title' => 'Partners',
            'module_action' => 'Edit',
            'module_icon' => 'fas fa-user-shield',
            'module_name' => 'partners',
        ]);
    }

    public function update(Request $request, $id)
    {
        $partner = User::where('group', 5)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:191',
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($partner->id),
            ],
            'username' => [
                'required',
                'string',
                'max:191',
                Rule::unique('users', 'username')->ignore($partner->id),
            ],
            'mobile' => 'required|string|max:30',
            'country' => 'required|exists:countrys,id',
            'password' => 'nullable|string|min:6|max:191',
            'allow_add_trainers' => 'required|integer|min:0',
            'no_of_license_purchased' => 'required|integer|min:0',
            'partnership_start_date' => 'required|date_format:Y-m-d',
            'partnership_end_date' => 'required|date_format:Y-m-d|after_or_equal:partnership_start_date',
            'currency_id' => 'required|integer|exists:currencies,id',
        ]);

        $oldPartnershipEndDate = $partner->partnership_end_date
            ? Carbon::parse($partner->partnership_end_date)->format('Y-m-d')
            : null;

        $partner->name = $validated['name'];
        $partner->email = $validated['email'];
        $partner->username = $validated['username'];
        $partner->mobile = $validated['mobile'] ?? null;
        $partner->country_id = $validated['country'];
        $partner->allow_add_trainers = $validated['allow_add_trainers'] ?? 0;
        $partner->no_of_license_purchased = $validated['no_of_license_purchased'];
        $partner->partnership_start_date = $validated['partnership_start_date'];
        $partner->partnership_end_date = $validated['partnership_end_date'];
        $partner->currency_id = $validated['currency_id'];
        $partner->group = 5;

        if (!empty($validated['password'])) {
            $partner->password = Hash::make($validated['password']);
        }

        $partner->save();

        $newPartnershipEndDate = Carbon::parse($partner->partnership_end_date)->format('Y-m-d');
        if ($oldPartnershipEndDate !== $newPartnershipEndDate) {
            $this->schedulePartnershipReminders($partner, true);
        }

        Log::info('Partner updated', [
            'partner_id' => $partner->id,
            'updated_by' => auth()->id(),
        ]);

        Flash::success('Partner updated successfully.')->important();

        return redirect()->route('backend.partners.index');
    }

    public function destroy($id)
    {
        $partner = User::where('group', 5)->findOrFail($id);
        $partner->delete();

        Log::info('Partner deleted', [
            'partner_id' => $partner->id,
            'deleted_by' => auth()->id(),
        ]);

        Flash::success('Partner deleted successfully.')->important();

        return redirect()->route('backend.partners.index');
    }

    public function checkUnique(Request $request)
    {
        $field = $request->get('field');
        $value = trim((string) $request->get('value'));
        $ignoreId = $request->get('ignore_id');

        if (!in_array($field, ['email', 'username'], true) || $value === '') {
            return response()->json([
                'exists' => false,
                'message' => 'Invalid uniqueness check.',
            ], 422);
        }

        $query = User::where($field, $value);

        if (!empty($ignoreId)) {
            $query->where('id', '!=', $ignoreId);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? ucfirst($field).' already exists.' : 'Available.',
        ]);
    }

    private function schedulePartnershipReminders(User $partner, bool $cancelExisting = false): void
    {
        if ($cancelExisting) {
            foreach ((array) $partner->partnership_reminder_job_ids as $jobId) {
                if ($jobId) {
                    DB::table('jobs')->where('id', $jobId)->delete();
                }
            }
        }

        $jobIds = [];
        $endDate = Carbon::parse($partner->partnership_end_date)->startOfDay();
        foreach ([3, 2, 1] as $months) {
            $job = new PartnerPartnershipReminder($partner->id);
            $job->delay($endDate->copy()->subMonthsNoOverflow($months));
            $jobId = null;
            safeDispatchAction('partner partnership reminder job', [
                'partner_id' => $partner->id,
                'reminder_months_before_end' => $months,
            ], function () use ($job, &$jobId) {
                $jobId = Bus::dispatch($job);
            });
            if ($jobId) {
                $jobIds[] = $jobId;
            }
        }

        $partner->partnership_reminder_job_ids = $jobIds;
        $partner->save();
    }
}
