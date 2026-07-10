<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\StoreResidentRequest;
use App\Http\Requests\Resident\UpdateResidentRequest;
use App\Jobs\ProcessBulkImport;
use App\Models\BulkImport;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Models\Wing;
use App\Notifications\ResidentWelcomeNotification;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Resident::class);

        try {

            if ($request->ajax()) {

                $user = auth()->user();

                $query = Resident::query()
                    ->select([
                        'residents.*',
                        'users.name as user_name',
                        'users.email as user_email',
                        'users.phone as user_phone',
                        'flats.flat_number as flat_number',
                        'wings.name as flat_wing',
                    ])
                    ->leftJoin('users', 'residents.user_id', '=', 'users.id')
                    ->leftJoin('flats', 'residents.flat_id', '=', 'flats.id')
                    ->leftJoin('wings', 'wings.id', '=', 'flats.wing_id');

                if (! $user->isSuperAdmin()) {
                    $query->where('flats.society_id', $user->society_id);
                }

                if ($user->isSuperAdmin() && $request->filled('society_id')) {
                    $query->where('flats.society_id', $request->society_id);
                }

                if ($request->filled('flat_id')) {
                    $query->where('residents.flat_id', $request->flat_id);
                }

                if ($request->filled('resident_type')) {
                    $query->where('residents.resident_type', $request->resident_type);
                }

                if ($request->filled('wing_id')) {
                    $query->where('flats.wing_id', $request->wing_id);
                }

                if ($user->isSuperAdmin() || $user->isAdmin()) {
                    match ($request->get('filter', 'active')) {
                        'deleted' => $query->onlyTrashed(),
                        'all' => $query->withTrashed(),
                        default => null,
                    };
                }

                return DataTables::of($query)
                    ->addIndexColumn()

                    ->addColumn('name', fn ($row) => $row->user_name ?? '-')
                    ->addColumn('email', fn ($row) => $row->user_email ?? '-')
                    ->addColumn('phone', fn ($row) => $row->user_phone ?? '-')

                    ->addColumn('flat', fn ($row) => $row->flat_number ?? '-')
                    ->addColumn('wing', fn ($row) => $row->flat_wing ?? '-')

                    ->addColumn('type', function ($row) {
                        return $row->resident_type === 'owner'
                            ? '<span class="badge bg-success">Owner</span>'
                            : '<span class="badge bg-info">Tenant</span>';
                    })

                    ->addColumn('actions', function ($row) {

                        $showUrl = route('residents.show', $row->id);
                        $editUrl = route('residents.edit', $row->id);
                        $deleteUrl = route('residents.destroy', $row->id);
                        $restore = route('residents.restore', $row->id);

                        $html = '<div class="d-flex justify-content-center gap-2">';

                        $html .= '
                            <a href="'.$showUrl.'" class="btn btn-info btn-sm text-white" title="View Resident">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                        ';

                        if (! $row->trashed()) {
                            $html .= '
                                  <a href="'.$editUrl.'" class="btn btn-primary btn-sm" title="Edit Resident"><i class="bi bi-pencil-fill"></i></a>

                                  <button
                                      class="btn btn-danger text-white btn-action btn-sm"
                                      data-url="'.$deleteUrl.'"
                                      data-method="DELETE"
                                      data-title="Delete Resident Details?"
                                      data-text="This action cannot be undone."
                                      data-confirm="Yes, Delete"
                                      data-success="Resident deleted successfully"
                                      title="Delete Flat">
                                      <i class="bi bi-trash-fill"></i>
                                  </button>
                            ';
                        } else {
                            $html .= '
                                  <button
                                      class="btn btn-secondary text-white btn-action btn-sm"
                                      data-url="'.$restore.'"
                                      data-method="PATCH"
                                      data-title="Restore Resident?"
                                      data-text="This resident details will be restored."
                                      data-confirm="Yes, Restore"
                                      title="Restore Resident">
                                          <i class="bi bi-arrow-up-left-circle-fill"></i>
                                  </button>
                            ';
                        }
                        $html .= '</div>';

                        return $html;
                    })

                    ->rawColumns(['type', 'actions'])
                    ->make(true);
            }

            $societies = auth()->user()->isSuperAdmin()
                ? Society::orderBy('name')->get()
                : collect();

            $flats = ! auth()->user()->isSuperAdmin()
                ? Flat::where('society_id', auth()->user()->society_id)->orderBy('wing')->orderBy('flat_number')->get()
                : collect();

            $wings = auth()->user()->isSuperAdmin()
                ? collect()
                : Wing::where('society_id', auth()->user()->society_id)->orderBy('name')->get();

            return view('residents.index', compact('societies', 'flats', 'wings'));

        } catch (Exception $e) {

            Log::error('Resident listing error: '.$e->getMessage());

            return back()->with([
                'message' => 'Something went wrong. Please try again.',
                'status' => 'error',
            ]);
        }
    }

    public function show(Resident $resident)
    {
        $this->authorize('view', $resident);

        try {
            $resident->load(['user', 'flat.wingRelation', 'flat.society']);

            $coResidents = Resident::where('flat_id', $resident->flat_id)
                ->where('id', '!=', $resident->id)
                ->with('user')
                ->get();

            return view('residents.show', compact('resident', 'coResidents'));
        } catch (Exception $e) {
            Log::error('Resident show page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong. Please try again.',
                'status' => 'error',
            ]);
        }
    }

    public function create()
    {
        $this->authorize('create', Resident::class);

        try {
            $user = auth()->user();

            $societies = $user->isSuperAdmin()
                ? Society::all()
                : collect();

            $flats = $user->isSuperAdmin()
                ? collect()
                : Flat::where('society_id', $user->society_id)->get();

            return view('residents.create', compact('societies', 'flats'));
        } catch (Throwable $e) {
            Log::error('Resident create page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong. Please try again.',
                'status' => 'error',
            ]);
        }
    }

    public function store(StoreResidentRequest $request)
    {
        $this->authorize('create', Resident::class);

        try {
            $data = $request->validated();

            $user = null;
            $resident = null;

            $residentRoleId = Role::where('name', 'resident')->value('id');

            $societyId = auth()->user()->isSuperAdmin()
                ? $data['society_id']
                : auth()->user()->society_id;

            DB::transaction(function () use (
                $data,
                $residentRoleId,
                &$user,
                &$resident,
                $societyId
            ) {

                $flatBelongsToSociety = Flat::where('id', $data['flat_id'])
                    ->where('society_id', $societyId)
                    ->exists();

                if (! $flatBelongsToSociety) {
                    throw new Exception('Invalid flat for selected society.');
                }

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => Str::password(32),
                    'role_id' => $residentRoleId,
                    'society_id' => $societyId,
                ]);

                $resident = Resident::create([
                    'user_id' => $user->id,
                    'flat_id' => $data['flat_id'],
                    'resident_type' => $data['resident_type'],
                ]);
            });

            if ($resident) {
                ActivityLogger::log('create', $resident, "Resident {$resident->user->name} was added to flat {$resident->flat->wing}-{$resident->flat->flat_number}.");
            }

            DB::afterCommit(function () use ($user) {
                if ($user) {
                    $user->notify(new ResidentWelcomeNotification($user));
                }
            });

            return redirect()->route('residents.index')->with([
                'message' => 'Resident created successfully.',
                'status' => 'success',
            ]);
        } catch (Throwable $e) {

            Log::error($e->getMessage());

            return back()->withInput()->with([
                'message' => 'Unable to create resident.'.$e->getMessage(),
                'status' => 'error',
            ]);
        }
    }

    public function edit(Resident $resident)
    {
        $this->authorize('update', $resident);

        try {
            $user = auth()->user();

            $societies = $user->isSuperAdmin()
                ? Society::all()
                : collect();

            $flats = Flat::where(
                'society_id',
                $resident->flat->society_id
            )->get();

            return view('residents.edit', compact('resident', 'flats', 'societies'));
        } catch (Throwable $e) {
            Log::error('Resident edit page error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with([
                'message' => 'Something went wrong. Please try again.',
                'status' => 'error',
            ]);
        }
    }

    public function update(
        UpdateResidentRequest $request,
        Resident $resident
    ) {
        $this->authorize('update', $resident);

        try {

            $data = $request->validated();

            DB::transaction(function () use (
                $resident,
                $data
            ) {
                $flat = Flat::findOrFail($data['flat_id']);

                $resident->user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'society_id' => $flat->society_id,
                ]);

                $resident->update([
                    'flat_id' => $data['flat_id'],
                    'resident_type' => $data['resident_type'],
                ]);
            });

            ActivityLogger::log('update', $resident, "Resident {$resident->user->name} details were updated.");

            return redirect()->route('residents.index')->with([
                'message' => 'Resident updated successfully.',
                'status' => 'success',
            ]);
        } catch (Throwable $e) {

            Log::error($e->getMessage());

            return back()->withInput()->with([
                'message' => 'Unable to update resident. Please try again.',
                'status' => 'error',
            ]);
        }
    }

    public function destroy(Resident $resident)
    {
        $this->authorize('delete', $resident);

        try {

            DB::transaction(function () use ($resident) {

                $user = $resident->user;
                $this->authorize('create', Resident::class);

                ActivityLogger::log('delete', $resident, "Resident {$resident->user->name} was removed.");

                $resident->delete();

                if ($user) {
                    $user->delete();
                }
            });

            return response()->json([
                'message' => 'Resident deleted successfully.',
                'success' => true,
            ]);
        } catch (Throwable $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function restore(Resident $resident)
    {
        $this->authorize('restore', $resident);

        try {

            $resident->restore();

            ActivityLogger::log('restore', $resident, 'resident restored.');

            return response()->json([
                'success' => true,
                'message' => 'Resident restored successfully.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore resident.',
            ]);
        }
    }

    public function showImportForm(Request $request)
    {
        $this->authorize('create', Resident::class);

        return view('residents.import');
    }

    public function downloadSampleCsv()
    {
        $this->authorize('create', Resident::class);

        $headers = ['Name', 'Email', 'Phone', 'Wing Name', 'Flat Number', 'Resident Type (owner/tenant)'];
        $sampleRows = [
            ['John Doe', 'john.doe@example.com', '9988776655', 'A', '101', 'owner'],
            ['Jane Smith', '', '9988776644', 'B', '202', 'tenant'],
            ['Robert Brown', 'robert.brown@example.com', '', 'A', '102', ''],
        ];

        $callback = function () use ($headers, $sampleRows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($sampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'residents_sample.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="residents_sample.csv"',
        ]);
    }

    public function handleUpload(Request $request)
    {
        $this->authorize('create', Resident::class);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('csv_file');
        $filename = time().'_'.Str::random(10).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('temp_bulk_uploads', $filename);

        $bulkImport = BulkImport::create([
            'user_id' => auth()->id(),
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'status' => 'pending',
        ]);

        $filePath = Storage::path($path);
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            fclose($handle);
        } else {
            return back()->with(['message' => 'Unable to read the uploaded file at: '.$filePath, 'status' => 'error']);
        }

        if (empty($headers)) {
            return back()->with(['message' => 'The uploaded file is empty.', 'status' => 'error']);
        }

        $headers = array_map(function ($h) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
        }, $headers);

        $societies = auth()->user()->isSuperAdmin() ? Society::all() : collect();

        return view('residents.import_map', compact('bulkImport', 'headers', 'societies'));
    }

    public function showValidationPreview(Request $request)
    {
        $this->authorize('create', Resident::class);

        $request->validate([
            'import_id' => ['required', 'exists:bulk_imports,id'],
            'map_name' => [$request->has('rows') ? 'nullable' : 'required', 'string'],
            'map_email' => [$request->has('rows') ? 'nullable' : 'required', 'string'],
            'map_phone' => [$request->has('rows') ? 'nullable' : 'required', 'string'],
            'map_flat_number' => [$request->has('rows') ? 'nullable' : 'required', 'string'],
            'map_wing' => [$request->has('rows') ? 'nullable' : 'required', 'string'],
            'map_resident_type' => ['nullable', 'string'],
            'default_resident_type' => ['required', 'in:owner,tenant'],
            'fallback_email_action' => ['required', 'in:generate,fail'],
            'fallback_phone_action' => ['required', 'in:generate,fail'],
            'society_id' => auth()->user()->isSuperAdmin() ? ['required', 'exists:societies,id'] : ['nullable'],
        ]);

        $bulkImport = BulkImport::findOrFail($request->import_id);
        $filePath = Storage::path('temp_bulk_uploads/'.$bulkImport->filename);

        if (! file_exists($filePath)) {
            return redirect()->route('residents.import.form')->with(['message' => 'Uploaded file not found at: '.$filePath, 'status' => 'error']);
        }

        $societyId = auth()->user()->isSuperAdmin() ? $request->society_id : auth()->user()->society_id;

        $rows = [];
        if ($request->has('rows')) {
            $submittedRows = $request->input('rows');
            foreach ($submittedRows as $r) {
                $rows[] = [
                    'name' => $r['name'] ?? '',
                    'email' => $r['email'] ?? '',
                    'phone' => $r['phone'] ?? '',
                    'wing' => $r['wing'] ?? '',
                    'flat_number' => $r['flat_number'] ?? '',
                    'resident_type' => $r['resident_type'] ?? '',
                ];
            }
            $map = [
                'name' => 'name',
                'email' => 'email',
                'phone' => 'phone',
                'flat_number' => 'flat_number',
                'wing' => 'wing',
                'resident_type' => 'resident_type',
            ];
        } else {
            if (($handle = fopen($filePath, 'r')) !== false) {
                $headers = fgetcsv($handle);
                $headers = array_map(function ($h) {
                    return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                }, $headers);

                while (($data = fgetcsv($handle)) !== false) {
                    if (count($data) <= count($headers)) {
                        $data = array_pad($data, count($headers), '');
                    }
                    $rows[] = array_combine($headers, array_slice($data, 0, count($headers)));
                }
                fclose($handle);
            }
            $map = [
                'name' => $request->map_name,
                'email' => $request->map_email,
                'phone' => $request->map_phone,
                'flat_number' => $request->map_flat_number,
                'wing' => $request->map_wing,
                'resident_type' => $request->map_resident_type,
            ];
        }

        $validatedRows = [];
        $isValid = true;
        $totalRows = count($rows);
        $validCount = 0;
        $invalidCount = 0;

        $csvEmails = [];
        $csvPhones = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $errors = [];

            $rawName = isset($row[$map['name']]) ? trim($row[$map['name']]) : '';
            $rawEmail = isset($row[$map['email']]) ? trim($row[$map['email']]) : '';
            $rawPhone = isset($row[$map['phone']]) ? trim($row[$map['phone']]) : '';
            $rawFlatNumber = isset($row[$map['flat_number']]) ? trim($row[$map['flat_number']]) : '';
            $rawWingName = isset($row[$map['wing']]) ? trim($row[$map['wing']]) : '';
            $rawType = ($map['resident_type'] && isset($row[$map['resident_type']])) ? strtolower(trim($row[$map['resident_type']])) : '';

            if (empty($rawType) || ! in_array($rawType, ['owner', 'tenant'])) {
                $rawType = $request->default_resident_type;
            }

            if (empty($rawName)) {
                $errors[] = 'Name is required.';
            } elseif (strlen($rawName) < 2 || strlen($rawName) > 100) {
                $errors[] = 'Name must be between 2 and 100 characters.';
            } elseif (! preg_match("/^[A-Za-z\s\.\'-]+$/", $rawName)) {
                $errors[] = 'Name format is invalid (letters and basic symbols only).';
            }

            if (empty($rawEmail)) {
                if ($request->fallback_email_action === 'generate') {
                    $slug = Str::slug($rawName ?: 'resident');
                    $rawEmail = $slug.'_'.rand(100, 999).'@dummy.societyms.test';
                } else {
                    $errors[] = 'Email is required.';
                }
            } elseif (! filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format.';
            } elseif (User::where('email', $rawEmail)->exists()) {
                $errors[] = "Email '{$rawEmail}' is already registered in the system.";
            } elseif (in_array($rawEmail, $csvEmails)) {
                $errors[] = "Duplicate email '{$rawEmail}' in the uploaded file.";
            } else {
                $csvEmails[] = $rawEmail;
            }

            if (empty($rawPhone)) {
                if ($request->fallback_phone_action === 'generate') {
                    $rawPhone = '99'.rand(10000000, 99999999);
                } else {
                    $errors[] = 'Phone is required.';
                }
            } elseif (strlen($rawPhone) < 7 || strlen($rawPhone) > 20) {
                $errors[] = 'Phone number must be between 7 and 20 digits.';
            } elseif (! preg_match("/^[0-9+\-\s()]+$/", $rawPhone)) {
                $errors[] = 'Phone format is invalid.';
            }

            $flatId = null;
            if (empty($rawFlatNumber) || empty($rawWingName)) {
                $errors[] = 'Wing and Flat Number are required to map to a flat.';
            } else {
                $wing = Wing::where('society_id', $societyId)
                    ->where('name', $rawWingName)
                    ->first();

                if (! $wing) {
                    $errors[] = "Wing '{$rawWingName}' does not exist in the selected society.";
                } else {
                    $flat = Flat::where('wing_id', $wing->id)
                        ->where('flat_number', $rawFlatNumber)
                        ->first();

                    if (! $flat) {
                        $errors[] = "Flat '{$rawFlatNumber}' does not exist inside Wing '{$rawWingName}'.";
                    } else {
                        $flatId = $flat->id;
                    }
                }
            }

            $rowValid = empty($errors);
            if ($rowValid) {
                $validCount++;
            } else {
                $invalidCount++;
                $isValid = false;
            }

            $validatedRows[] = [
                'row_number' => $rowNum,
                'name' => $rawName,
                'email' => $rawEmail,
                'phone' => $rawPhone,
                'wing' => $rawWingName,
                'flat_number' => $rawFlatNumber,
                'flat_id' => $flatId,
                'resident_type' => $rawType,
                'valid' => $rowValid,
                'errors' => $errors,
            ];
        }

        $validatedFilename = 'validated_'.$bulkImport->id.'.json';
        Storage::put('temp_bulk_uploads/'.$validatedFilename, json_encode($validatedRows));

        $bulkImport->update([
            'total_rows' => $totalRows,
            'status' => 'validated',
        ]);

        $showEditor = ($invalidCount <= 10);

        return view('residents.import_preview', compact('bulkImport', 'validatedRows', 'isValid', 'totalRows', 'validCount', 'invalidCount', 'societyId', 'showEditor'));
    }

    public function processImport(Request $request)
    {
        $this->authorize('create', Resident::class);

        $request->validate([
            'import_id' => ['required', 'exists:bulk_imports,id'],
            'society_id' => ['required', 'exists:societies,id'],
        ]);

        $bulkImport = BulkImport::findOrFail($request->import_id);
        $jsonFilename = 'validated_'.$bulkImport->id.'.json';
        $jsonPath = 'temp_bulk_uploads/'.$jsonFilename;

        if (! Storage::exists($jsonPath)) {
            return redirect()->route('residents.import.form')->with(['message' => 'Validated data file not found.', 'status' => 'error']);
        }

        $validatedRows = json_decode(Storage::get($jsonPath), true);
        $hasInvalidRows = collect($validatedRows)->contains('valid', false);
        if ($hasInvalidRows) {
            return back()->with(['message' => 'Cannot import file with validation errors. Please fix errors first.', 'status' => 'error']);
        }

        ProcessBulkImport::dispatch(
            $bulkImport->id,
            $request->society_id,
            auth()->id()
        );

        $bulkImport->update(['status' => 'processing']);

        return redirect()->route('residents.index')->with([
            'message' => 'Resident bulk import has been queued and will process in the background. You will receive a system notification once complete.',
            'status' => 'success',
        ]);
    }

    public function downloadErrorReport($importId)
    {
        $this->authorize('create', Resident::class);

        $bulkImport = BulkImport::findOrFail($importId);
        $jsonFilename = 'validated_'.$bulkImport->id.'.json';
        $jsonPath = 'temp_bulk_uploads/'.$jsonFilename;

        if (! Storage::exists($jsonPath)) {
            return redirect()->route('residents.import.form')->with(['message' => 'Validation error history not found.', 'status' => 'error']);
        }

        $validatedRows = json_decode(Storage::get($jsonPath), true);

        $headers = ['Row Number', 'Name', 'Email', 'Phone', 'Wing Name', 'Flat Number', 'Resident Type', 'Validation Errors'];

        $callback = function () use ($headers, $validatedRows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($validatedRows as $row) {
                if (! $row['valid']) {
                    fputcsv($file, [
                        $row['row_number'],
                        $row['name'],
                        $row['email'],
                        $row['phone'],
                        $row['wing'],
                        $row['flat_number'],
                        $row['resident_type'],
                        implode('; ', $row['errors']),
                    ]);
                }
            }
            fclose($file);
        };

        $exportName = 'error_report_'.str_replace('.csv', '', $bulkImport->original_filename).'.csv';

        return response()->streamDownload($callback, $exportName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$exportName.'"',
        ]);
    }
}
