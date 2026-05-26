<ul class="navbar-nav iq-main-menu sidebar-menu-simplified" id="sidebar">
    {{-- Home --}}
    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Home</span>
            <span class="mini-icon">-</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ activeMenu('dashboard') }}" href="{{ route('dashboard') }}">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 10.5L12 4L21 10.5V19C21 19.5523 20.5523 20 20 20H15V14H9V20H4C3.44772 20 3 19.5523 3 19V10.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>
            </i>
            <span class="item-name">Beranda</span>
        </a>
    </li>

    <li><hr class="hr-horizontal"></li>

    {{-- Pages --}}
    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Pages</span>
            <span class="mini-icon">-</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link sidebar-menu-page {{ activeMenu(['special-pages.pendaftar', 'special-pages.pendaftar.show', 'special-pages.pendaftar.verifikasi']) }}" href="{{ route('special-pages.pendaftar') }}">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5C15 6.10457 14.1046 7 13 7H11C9.89543 7 9 6.10457 9 5Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </i>
            <span class="item-name">Verifikasi Pendaftar</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link sidebar-menu-page {{ activeMenu(['nilai-akhir.index', 'nilai-akhir.detail', 'nilai-akhir.refresh', 'nilai-akhir.export', 'nilai-akhir.export.csv']) }}" href="{{ route('nilai-akhir.index') }}">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 9L12 5L20 9L12 13L4 9Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    <path d="M6 10.5V15.5C6 15.5 8.5 17.5 12 17.5C15.5 17.5 18 15.5 18 15.5V10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M20 9V13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </i>
            <span class="item-name">Penilaian Akhir</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link sidebar-menu-page {{ activeMenu(['tambah-admin.index', 'tambah-admin.edit']) }}" href="{{ route('tambah-admin.index') }}">
            <i class="icon">
                <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 20V18C17 16.3431 15.6569 15 14 15H6C4.34315 15 3 16.3431 3 18V20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <circle cx="10" cy="8" r="4" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M21 20V18C21 17.11 20.5565 16.3151 19.9 15.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M16 4.13C16.8604 4.61028 17.5 5.56989 17.5 6.75C17.5 7.93011 16.8604 8.88972 16 9.37" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </i>
            <span class="item-name">Kelola Admin</span>
        </a>
    </li>
</ul>
