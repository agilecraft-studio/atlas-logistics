{{-- Legacy global JS ported from the old layouts/app.blade.php so unconverted/converted
     legacy views keep working inside the Attex shell:
       * CSRF header for every jQuery AJAX call
       * automatic DataTables for #data_table / #data_table1 / #data_table11 / #ajax_data_table footer filters
       * route + label JS variables the legacy page scripts expect
       * jQuery Validate
     Included from layouts/attex/footer-script.blade.php AFTER legacy-scripts and BEFORE page scripts. --}}
<script>
    // Routes and Labels (used by legacy page scripts)
    var getDriverRoute = '{{ url('admin/get_driver') }}';
    var getVehicleRoute = '{{ url('admin/get_vehicle') }}';
    var prevAddress = '{{ url('admin/prev-address') }}';
    var selectDriver = "@lang('fleet.selectDriver')";
    var selectCustomer = "@lang('fleet.selectCustomer')";
    var selectVehicle = "@lang('fleet.selectVehicle')";
    var addCustomer = "@lang('fleet.add_customer')";
    var prevAddressLang = "@lang('fleet.prev_addr')";
    var fleet_email_already_taken = "@lang('fleet.email_already_taken')";
    var driver_comission_amount = "@lang('fleet.enter_amount')";
    var driver_comission_percentage = "@lang('fleet.enter_percent')";
    var group_id_label = "@lang('fleet.selectGroup')";
    var role_id_label = "@lang('fleet.role')";
    var current_route = "{{ optional(Route::current())->getName() ?? '' }}";
    var base_url = '{{ url('/') }}';
</script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            // $('button').on('click', function() {
            //   if (!$(this).data('clicked')) {
            //     console.log('Button clicked!');
            //     $(this).data('clicked', true);
            //     $(this).prop('disabled', true);
            //   }
            // });

            $('#phone').on('input', function(evt) {

                var inputVal = $(this).val();

                var cleanedVal = inputVal.replace(/[^0-9.]/g, '').replace(/^0+/, '');

                if (cleanedVal.length > 15) {

                    cleanedVal = cleanedVal.slice(0, 15);

                }

                $(this).val(cleanedVal);

            });



            $('input[type="number"]').on('keypress', function(evt) {

                var inputVal = $(this).val();

                var ASCIICode = (evt.which) ? evt.which : evt.keyCode;



                if (inputVal.length >= 15) {

                    return false;

                }

                if (ASCIICode === 46 && inputVal.length === 0) {

                    return false;

                }

                if (ASCIICode === 46 || (ASCIICode >= 48 && ASCIICode <= 57)) {

                    return true;

                }

                return false;

            });



            $('input[type="text"]').on('keypress', function(evt) {

                var inputVal = $(this).val();

                var ASCIICode = (evt.which) ? evt.which : evt.keyCode;

                var inputId = $(this).attr("id");



                if (

                    (ASCIICode >= 65 && ASCIICode <= 90) || // A-Z

                    (ASCIICode >= 97 && ASCIICode <= 122) || // a-z

                    ASCIICode === 32 || // Space

                    ASCIICode === 44 || // Comma

                    ASCIICode === 45 || // Hyphen (minus)

                    ASCIICode === 46 || // Period

                    ASCIICode === 47 || // Slash

                    (ASCIICode >= 48 && ASCIICode <= 57) || // Digits 0-9

                    ASCIICode === 58 || // Colon

                    ASCIICode === 64 || // At symbol (@)

                    (inputId === "currency" && [36, 8364, 163, 165, 8377, 165, 8355, 8363].includes(
                        ASCIICode)) // Currency symbols

                ) {

                    return true;

                }

                return false;

            });



            // $('input').on('input', function(evt) {

            //   var inputVal = $(this).val();

            //   // Allow alphanumeric characters (A-Z, a-z, 0-9), specific symbols (-, /, ., ,, :, space, and @)

            //   var cleanedVal = inputVal.replace(/[^A-Za-z0-9\/\.\,\:\-\s@]/g, '')

            //   $(this).val(cleanedVal);

            // });



            $("input[type=search]").on("input", function() {

                const inputValue = this.value.trim(); // Trim the input value to remove whitespace

                if (inputValue.length > 0) {

                    $(".nav-sidebar li").hide().filter(function() {

                        return $(this).text().toLowerCase().indexOf(inputValue.toLowerCase()) !== -
                            1;

                    }) //.show();

                } else {

                    $(".nav-sidebar li").show();

                }

            });



            // old search input code

            // $("input[type=search]").on("keydown", function () {

            //   if (this.value.length > 0) {

            //     $(".nav-sidebar li").hide().filter(function () {

            //       return $(this).text().toLowerCase().indexOf($("input[type=search]").val().toLowerCase()) != -1;

            //     })//.show();

            //   }

            //   else {

            //     $(".nav-sidebar li").show();

            //   }

            // });



            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });



            $('#data_table tfoot th').each(function() {

                // console.log($('#data_table tfoot th').length);

                if ($(this).index() != 0 && $(this).index() != $('#data_table tfoot th').length - 1) {

                    var title = $(this).text();

                    $(this).html('<input type="text" placeholder="' + title + '" />');

                }

            });



            $('#ajax_data_table tfoot th').each(function() {

                // console.log($('#data_table tfoot th').length);

                if ($(this).index() != 0 && $(this).index() != $('#data_table tfoot th').length - 1) {

                    var title = $(this).text();

                    $(this).html('<input type="text" placeholder="' + title + '" />');

                }

            });



            $('#data_table1 tfoot th').each(function() {

                // console.log($(this).index());

                if ($(this).index() != 0 && $(this).index() != $('#data_table1 tfoot th').length - 1) {

                    var title = $(this).text();

                    $(this).html('<input type="text" placeholder="' + title + '" />');

                }

            });



            var table1 = $('#data_table1').DataTable({

                dom: 'Bfrtip',



                buttons: [

                    {

                        extend: 'print',

                        text: '<i class="fa fa-print"></i> {{ __('fleet.print') }}',



                        exportOptions: {

                            columns: ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),

                        },

                        customize: function(win) {

                            $(win.document.body)

                                .css('font-size', '10pt')

                                .prepend(

                                    '<h3>{{ __('fleet.bookings') }}</h3>'

                                );

                            $(win.document.body).find('table')

                                .addClass('table-bordered');

                            // $(win.document.body).find( 'td' ).css( 'font-size', '10pt' );



                        }

                    }

                ],

                "language": {

                    "url": '{{ asset('assets/datatables/') . '/' . __('fleet.datatable_lang') }}',

                },

                columnDefs: [{
                    orderable: false,
                    targets: [0]
                }],

                // individual column search

                "initComplete": function() {

                    table1.columns().every(function() {

                        var that = this;

                        $('input', this.footer()).on('keyup change', function() {

                            that.search(this.value).draw();

                        });

                    });

                }

            });



            var table = $('#data_table').DataTable({

                "language": {

                    "url": '{{ asset('assets/datatables/') . '/' . __('fleet.datatable_lang') }}',

                },


                columnDefs: [{
                    orderable: false,
                    targets: [0]
                }],


                // individual column search

                "initComplete": function() {

                    table.columns().every(function() {

                        var that = this;

                        $('input', this.footer()).on('keyup change', function() {

                            // console.log($(this).parent().index());

                            that.search(this.value).draw();

                        });

                    });

                }

            });

            $('[data-toggle="tooltip"]').tooltip();



        });






        var table11 = $('#data_table11').DataTable({
            "language": {
                "url": '{{ asset('assets/datatables/') . '/' . __('fleet.datatable_lang') }}',
            },
            "columnDefs": [{
                    orderable: false,
                    targets: [0]
                } // Make the first column not orderable
            ],
            "order": [
                [3, 'desc']
            ], // Order by the fourth column in descending order (index 3)


        });

        $('[data-toggle="tooltip"]').tooltip();
    </script>
