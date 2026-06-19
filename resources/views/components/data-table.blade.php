<div class="table-responsive">

    <table
        id="{{ $id }}"
        class="table table-bordered table-striped w-100">

        {{ $slot }}

    </table>

</div>

{{-- no need of script in component every table has defined js in that page only --}}