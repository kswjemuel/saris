<nav class="navbar navbar-default navbar-fixed-top custom-nav">
      <div class="container-fluid">
        

        <!-- Brand and toggle get grouped for better mobile display -->

        <div class="navbar-header">

            <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">

                <span class="sr-only">Toggle navigation</span>

                <span class="icon-bar"></span>

                <span class="icon-bar"></span>

                <span class="icon-bar"></span>

            </button>

        </div>

        <!-- Collection of nav links, forms, and other content for toggling -->

        <div id="navbarCollapse" class="collapse navbar-collapse">

            <!-- <ul class="nav navbar-nav">

                <li class="active"><a href="#">Home</a></li>

                <li><a href="#">Profile</a></li>

                

            </ul> -->

            <h2 class="navbar-left page-name">@yield('page-name')</h2>

            <form class="navbar-form navbar-left">

                <div class="input-group">

                    <input id="searchbox" type="text" class="form-control" placeholder="Search">

                    <span class="input-group-btn">

                        <button type="button" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>

                    </span>

                </div>

            </form>


            <ul class="nav navbar-nav navbar-right mr-25">

                <li class="dropdown">

                    <a data-toggle="dropdown" class="dropdown-toggle" href="javascript:void(0)">{{Auth::user()->name}} <i class="dripicons-chevron-down "></i></a>

                    <ul class="dropdown-menu">

                        <li><a href="#">Option 1</a></li>
                        <li><a href="#">Option 2</a></li>

                        <li class="divider"></li>

                        <li><a href="{{route('logout')}}">Logout</a></li>

                    </ul>

                </li>

            </ul>

        </div>

    
      </div>
    </nav>