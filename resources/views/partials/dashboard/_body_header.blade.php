<nav class="nav navbar navbar-expand-lg navbar-light iq-navbar">
  <div class="container-fluid navbar-inner">
    <a href="{{route('dashboard')}}" class="navbar-brand">
      <img src="{{asset('images/logo/logo.png')}}" alt="Inovasi Sosial Logo" width="30" height="30" class="me-2">
      <h4 class="logo-title text-uppercase">Inovasi Sosial</h4>
    </a>
    <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
      <i class="icon">
      </i>
    </div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
      <span class="navbar-toggler-icon">
        <span class="navbar-toggler-bar bar1 mt-2"></span>
        <span class="navbar-toggler-bar bar2"></span>
        <span class="navbar-toggler-bar bar3"></span>
      </span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto  navbar-list mb-2 mb-lg-0">



        <li class="nav-item dropdown">
          <a class="nav-link py-0 d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="{{asset('images/avatars/01.png')}}" alt="User-Profile" class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded">
          <img src="{{asset('images/avatars/avtar_1.png')}}" alt="User-Profile" class="theme-color-purple-img img-fluid avatar avatar-50 avatar-rounded">
          <img src="{{asset('images/avatars/avtar_2.png')}}" alt="User-Profile" class="theme-color-blue-img img-fluid avatar avatar-50 avatar-rounded">
          <img src="{{asset('images/avatars/avtar_4.png')}}" alt="User-Profile" class="theme-color-green-img img-fluid avatar avatar-50 avatar-rounded">
          <img src="{{asset('images/avatars/avtar_5.png')}}" alt="User-Profile" class="theme-color-yellow-img img-fluid avatar avatar-50 avatar-rounded">
          <img src="{{asset('images/avatars/avtar_3.png')}}" alt="User-Profile" class="theme-color-pink-img img-fluid avatar avatar-50 avatar-rounded">
            <div class="caption ms-3 d-none d-md-block ">
              <h6 class="mb-0 caption-title">{{ auth()->user()->name ?? auth()->user()->full_name ?? 'User' }}</h6>
              <p class="mb-0 caption-sub-title text-capitalize">
                @if(auth()->check())
                    @if(auth()->user()->role)
                        {{ ucfirst(auth()->user()->role) }}
                    @elseif(auth()->user()->user_type)
                        {{ str_replace('_',' ',auth()->user()->user_type) }}
                    @else
                        User
                    @endif
                @else
                    Guest
                @endif
              </p>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li>
              <div class="dropdown-item">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <img src="{{asset('images/avatars/01.png')}}" alt="User-Profile" class="img-fluid avatar avatar-40 avatar-rounded">
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-0">{{ auth()->user()->name ?? auth()->user()->full_name ?? 'User' }}</h6>
                    <small class="text-muted">{{ auth()->user()->email ?? 'No email' }}</small>
                    <br>
                    <small class="text-muted">
                      @if(auth()->check())
                          @if(auth()->user()->role)
                              {{ ucfirst(auth()->user()->role) }}
                          @elseif(auth()->user()->user_type)
                              {{ str_replace('_',' ',auth()->user()->user_type) }}
                          @else
                              User
                          @endif
                      @else
                          Guest
                      @endif
                    </small>
                  </div>
                </div>
              </div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a href="{{ \App\Helpers\DashboardHelper::getLandingUrl() }}" class="dropdown-item" target="_blank" rel="noopener">
                Buka Landing Page
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><form method="POST" action="{{route('logout')}}">
              @csrf
              <a href="javascript:void(0)" class="dropdown-item"
                onclick="event.preventDefault();
              this.closest('form').submit();">
                  {{ __('Log out') }}
              </a>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>



