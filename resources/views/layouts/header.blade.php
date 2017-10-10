    <header class="main-header">
        <div class="logo"><a href="//keepo.me"><span>Keepo</span></a></div>

        <nav class="main-nav">
            <a href="{{ url('contents') }}" class="{{ $activeNav == 'contents' || '' ? 'active' : '' }}">Contents</a>
            <a href="{{ url('authors') }}" class="{{ $activeNav == 'authors' ? 'active' : '' }}">Authors</a>
            <a href="{{ url('channels') }}" class="{{ $activeNav == 'channels' ? 'active' : '' }}">Channels &amp; Formats</a>
            <a href="{{ url('responses') }}" class="{{ $activeNav == 'responses' ? 'active' : '' }}">Responses</a>
            <a href="{{ url('embeds') }}" class="{{ $activeNav == 'embeds' ? 'active' : '' }}">Embeds</a>
            <a href="{{ url('tags') }}" class="{{ $activeNav == 'tags' ? 'active' : '' }}">Tags</a>
            <a href="{{ url('ads') }}" class="{{ $activeNav == 'ads' ? 'active' : '' }}">Ads</a>
            <a href="{{ url('totalizer') }}" class="{{ $activeNav == 'totalizer' ? 'active' : '' }}">Totalizer</a>
            <a href="{{ url('flush-cache') }}" class="{{ $activeNav == 'flush-cache' ? 'active' : '' }}">Flush Cache</a>
            <a href="{{ url('logout') }}">Logout</a>
        </nav>
    </header>