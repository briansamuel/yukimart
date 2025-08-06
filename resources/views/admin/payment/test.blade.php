@extends('admin.main-content')

@section('title', 'Test Payments')

@section('content')
<div class="container">
    <h1>Test Payments AJAX</h1>
    <button id="test-btn" class="btn btn-primary">Test AJAX Call</button>
    <div id="result" class="mt-3"></div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#test-btn').click(function() {
        $.ajax({
            url: '{{ route("admin.payment.ajax") }}',
            type: 'GET',
            data: {
                page: 1,
                per_page: 5
            },
            success: function(response) {
                $('#result').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
            },
            error: function(xhr, status, error) {
                $('#result').html('Error: ' + error + '<br>Status: ' + status + '<br>Response: ' + xhr.responseText);
            }
        });
    });
});
</script>
@endsection
