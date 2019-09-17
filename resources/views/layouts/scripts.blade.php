<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


{{--<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>--}}
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('fancybox/source/jquery.fancybox.js') }}"></script>
<script src="{{ asset('js/slick.min.js') }}"></script>
<script src="{{asset('js/jquery.plugin.js')}}"></script>


<script src="{{ asset('/js/jquery.maskedinput.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/main.js') }}"></script>

<!--[if lt IE 9]>
<script src="{{ asset('libs/html5shiv/es5-shim.min.js') }}"></script>
<script src="{{ asset('libs/html5shiv/html5shiv.min.js') }}"></script>
<script src="{{ asset('libs/html5shiv/html5shiv-printshiv.min.js') }}"></script>
<script src="{{ asset('libs/respond/respond.min.js') }}"></script>
<![endif]-->

<script type="text/javascript" src="{{ asset('js/jquery.slicebox.js') }}"></script>
<script type="text/javascript">
    $(function() {

        var Page = (function() {

            // var $navArrows = $( '#nav-arrows' ).hide(),
                // $shadow = $( '#shadow' ).hide(),
               var slicebox = $( '.sb-slider' ).slicebox( {
                    onReady : function() {

                    },
                    orientation : 'h',
                    cuboidsCount : 3,
                    // cuboidsRandom : true,
                    autoplay: true,
                    speed: 1000,
                    fallbackFadeSpeed: 1000,
                    sequentialFactor: 100,
                    colorHiddenSides : '#7d0a32',
                    interval: 5000
                } ),

                init = function() {

                    initEvents();

                },
                initEvents = function() {

                    // add navigation events
                    $navArrows.children( ':first' ).on( 'click', function() {

                        slicebox.next();
                        return false;

                    } );

                    $navArrows.children( ':last' ).on( 'click', function() {

                        slicebox.previous();
                        return false;

                    } );

                };

            return { init : init };

        })();

        Page.init();

    });
</script>
