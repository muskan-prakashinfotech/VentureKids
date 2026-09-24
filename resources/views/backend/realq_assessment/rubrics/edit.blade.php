@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="mb-5 container-fluid">
                <form action="{{ route('backend.realqassessment.rubrics.update') }}" method="POST" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Rubric</h4>
                        <a href="{{ route('backend.realqassessment.rubrics.index') }}" class="btn btn-warning float-right">
                            <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                        </a>
                    </div>
                    <div class="card-body table-responsive">
                        @csrf
                        <div class="form-group">
                            <label for="scale_id">Scale</label>
                            <select class="form-control @error('scale_id') is-invalid @enderror" id="scale_id" name="scale_id" required>
                                <option value="">Select Scale</option>
                                @foreach ($scales as $scale)
                                    <option value="{{ $scale->id }}" {{ (string) old('scale_id', $rubric->scale_id) === (string) $scale->id ? 'selected' : '' }}>
                                        {{ $scale->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scale_id')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        @php
                            $oldRubrics = old('rubrics', [[
                                'id' => $rubric->id,
                                'name' => $rubric->name,
                                'description' => $rubric->description,
                                'score' => $rubric->score,
                            ]]);
                        @endphp
                        <table class="table table-bordered" id="rubricRowsTable">
                            <thead>
                                <tr>
                                    <th>Rubric Name</th>
                                    <th>Description</th>
                                    <th style="width: 140px;">Score</th>
                                    <th style="width: 90px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($oldRubrics as $index => $rubricRow)
                                    <tr class="rubric-row">
                                        <td>
                                            <input type="hidden" name="rubrics[{{ $index }}][id]" value="{{ $rubricRow['id'] ?? '' }}">
                                            <input type="text" class="form-control @error('rubrics.' . $index . '.name') is-invalid @enderror"
                                                name="rubrics[{{ $index }}][name]" placeholder="Rubric Name"
                                                value="{{ $rubricRow['name'] ?? '' }}" required>
                                            @error('rubrics.' . $index . '.name')
                                                <strong class="text-danger">{{ $message }}</strong>
                                            @enderror
                                        </td>
                                        <td>
                                            <textarea class="form-control @error('rubrics.' . $index . '.description') is-invalid @enderror"
                                                name="rubrics[{{ $index }}][description]" rows="2" placeholder="Description" required>{{ $rubricRow['description'] ?? '' }}</textarea>
                                            @error('rubrics.' . $index . '.description')
                                                <strong class="text-danger">{{ $message }}</strong>
                                            @enderror
                                        </td>
                                        <td>
                                            <select class="form-control @error('rubrics.' . $index . '.score') is-invalid @enderror"
                                                name="rubrics[{{ $index }}][score]" required>
                                                <option value="">Score</option>
                                                @for ($i = 1; $i <= 10; $i++)
                                                    <option value="{{ $i }}" {{ (string) ($rubricRow['score'] ?? '') === (string) $i ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
                                            @error('rubrics.' . $index . '.score')
                                                <strong class="text-danger">{{ $message }}</strong>
                                            @enderror
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger remove-rubric-row"
                                                data-existing="{{ !empty($rubricRow['id']) ? '1' : '0' }}"
                                                {{ count($oldRubrics) === 1 || !empty($rubricRow['id']) ? 'disabled' : '' }}>
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success mt-2" id="addRubricRow">
                            <i class="fas fa-plus"></i> Add More
                        </button>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-stream-submit">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableBody = document.querySelector('#rubricRowsTable tbody');
            const addButton = document.getElementById('addRubricRow');
            let nextRubricIndex = tableBody.querySelectorAll('tr').length;

            function updateRemoveButtons() {
                const buttons = tableBody.querySelectorAll('.remove-rubric-row');
                buttons.forEach(button => {
                    button.disabled = buttons.length === 1 || button.dataset.existing === '1';
                });
            }

            function scoreOptions() {
                let options = '<option value="">Score</option>';
                for (let i = 1; i <= 10; i++) {
                    options += `<option value="${i}">${i}</option>`;
                }
                return options;
            }

            addButton.addEventListener('click', function () {
                const index = nextRubricIndex++;
                const row = document.createElement('tr');
                row.className = 'rubric-row';
                row.innerHTML = `
                    <td>
                        <input type="hidden" name="rubrics[${index}][id]" value="">
                        <input type="text" class="form-control" name="rubrics[${index}][name]" placeholder="Rubric Name" required>
                    </td>
                    <td>
                        <textarea class="form-control" name="rubrics[${index}][description]" rows="2" placeholder="Description" required></textarea>
                    </td>
                    <td>
                        <select class="form-control" name="rubrics[${index}][score]" required>${scoreOptions()}</select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-rubric-row" data-existing="0">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
                updateRemoveButtons();
            });

            tableBody.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-rubric-row');
                if (!button || button.disabled) {
                    return;
                }
                button.closest('tr').remove();
                updateRemoveButtons();
            });

            updateRemoveButtons();
        });
    </script>
@endsection
