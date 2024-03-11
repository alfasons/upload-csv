<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Include the CSRF token meta tag here -->

    <title>Import Contacts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importModal">Import Contacts</button>

    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Contacts</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Step 1: Upload CSV -->
                    <div class="import-step" id="step1">
                        <form id="uploadForm">
                            <div class="form-group">
                                <label for="csv_file">Upload CSV file</label>
                                <input type="file" class="form-control-file" id="csv_file" name="csv_file" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                    <!-- Step 2: Map Columns -->
                    <div class="import-step" id="step2" style="display: none;">
                        <H4>Map Columns</H4>
                        <div class="table table-responsive">
                            <form id="mappingForm">
                                <input type="hidden" name="filePath" id="filePath">
                                <table class="table">
                                    <thead>
                                        <tr id="csvHeaders"></tr>
                                    </thead>
                                    <tbody id="csvPreview"></tbody>
                                </table>
                                <button type="submit" class="btn btn-success">Import</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 <!-- Your content goes here -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

    <!-- Popper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
     // Set up AJAX headers with CSRF token
     $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#uploadForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: '{{ route("contacts.upload") }}',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#step1').hide();
                $('#filePath').val(response.filePath);
                $('#csvHeaders').empty();
                response.headers.forEach(header => {
                    $('#csvHeaders').append(`<th>${header}<select name="mapping[${header}]" class="form-control column-mapping">
    @foreach ($dbColumns as $column)
        <option value="{{ $column }}">{{ $column }}</option>
    @endforeach
</select></th>`);
                });

                $('#csvPreview').empty();
                response.sampleData.forEach(row => {
                    var rowHtml = '<tr>';
                    row.forEach(cell => {
                        rowHtml += `<td>${cell}</td>`;
                    });
                    rowHtml += '</tr>';
                    $('#csvPreview').append(rowHtml);
                });
                $('#step2').show();
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
     // Function to update dropdown options based on selections
     function updateDropdowns() {
        // First, enable all options
        $('.column-mapping option').prop('disabled', false);

        // Then, disable options that are already selected in other dropdowns
        $('.column-mapping').each(function() {
            var selectedValue = $(this).val();
            if (selectedValue) {
                $('.column-mapping').not(this).find(`option[value="${selectedValue}"]`).prop('disabled', true);
            }
        });
    }

    // Call updateDropdowns whenever any dropdown changes
    $(document).on('change', '.column-mapping', function() {
        updateDropdowns();
    });

    $('#mappingForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: '{{ route("contacts.processMapping") }}',
            data: formData,
            success: function(response) {
                $('#importModal').modal('hide');
                alert('Contacts imported successfully!');
                // Reset and prepare for next import if necessary
                $('#uploadForm')[0].reset();
                $('#mappingForm')[0].reset();
                $('#step2').hide();
                $('#step1').show();
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
});
</script>

</body>
</html>
