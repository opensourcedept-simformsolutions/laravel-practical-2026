<form method="POST" action="{{ route('logout') }}">
    @csrf

    <button type="submit" class="logout-btn">
        Logout
    </button>
</form>