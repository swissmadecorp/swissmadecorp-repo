<div>
    <button data-modal-target="calendar" data-modal-toggle="calendar" class="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-300 bg-white px-5 py-3 text-sm font-semibold text-stone-800 transition hover:border-stone-900 hover:text-stone-900">Book an Appointment</button>
    <div wire:ignore id="calendar" tabindex="-1" aria-hidden="true" class="backdrop-blur bg-black/40 fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 max-h-full">
        <div class="relative w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b rounded-t p-2 bg-gray-700 dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-white dark:text-white">
                        Make appointment
                    </h3>
                    <button id="calendar-close-button" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="calendar">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->

                <div class="border col-span-2 shadow-sm sm:col-span-1">
                    <div id="calendar_container">
                        <div class="calendar"></div>
                        <div class="selected_date"></div>
                        <div class="grid grid-cols-3 gap-1 time_selection p-3"></div>
                    </div>
                </div>

                <div id="contact_container" class="col-span-2 sm:col-span-1 hidden p-2">
                    <h3 class="text-left text-2xl mb-2">Contact Information</h3>
                    <a href="#" class="block mb-3 mt-3 w-32"><i class="fa fa-chevron-left"></i> Change date</a>

                    <div class=" pb-2.5">
                        <div class="items-center">
                            <label for="contact_name" class="text-left block text-sm font-medium text-gray-900 dark:text-white w-32">Contact Name</label>
                            <input id="contact_name" wire:model="calendar.contact_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                        </div>
                    </div>
                    <div class=" pb-2.5">
                        <div class="items-center">
                            <label for="phone" class="text-left block text-sm font-medium text-gray-900 dark:text-white w-32">Phone</label>
                            <input id="phone" wire:model="calendar.phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        </div>
                    </div>
                    <div class=" pb-2.5">
                        <div class="items-center">
                            <label for="email" class="text-left block text-sm font-medium text-gray-900 dark:text-white w-32">Email</label>
                            <input id="calendar_email" wire:model="calendar.calendar_email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                        </div>
                    </div>

                    <div id="appointment" class="bg-gray-100 flex font-semibold items-center justify-between m-1 p-5 shadow-md">
                        <div id="date" class="col-9"></div>
                        <div class="col-3">
                            <button wire:click.prevent="Book()" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="1" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Book
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        let currentDate = new Date();

        $(document).ready(function() {
            // 1. Define the Current Date properly
            var todayStr = moment().format('YYYY-MM-DD');

            // 2. RUN IMMEDIATELY on page load
            // This ensures buttons appear before the user even touches the calendar
            $('.selected_date').text('Book on ' + moment().toDate().toDateString());
            initTime();

            // This is called "Event Delegation"
            // It listens for clicks on .time_selection, but only triggers if a .selected_time was clicked
            $('.time_selection').on('click', '.selected_time', function(e) {
                e.preventDefault(); // Prevent page jump

                // Get the time text (e.g., "4:00 pm")
                let selectedTime = $(this).text();

                // 1. Mark it as active visually (optional)
                $('.selected_time').removeClass('active');
                $(this).addClass('active');

                // 2. Set the time in your backend/Livewire
                @this.set('bookTime', selectedTime);

                // 3. Navigate to the next screen
                // If your original code used a function to go to the next step, call it here.
                // Example: goToNextStep();

                console.log("Time selected: " + selectedTime);

                // If you need to trigger a specific Livewire action to move screens:
                // @this.call('submitSelection');
            });

            // 3. Initialize the Calendar
            $('.calendar').pignoseCalendar({
                format: 'MM/DD/YYYY',
                init: function (context) {
                    // Optional: You can leave this empty if the code above is running
                },
                disabledWeekdays: [0, 5, 6],
                disabledRanges: [
                    ['2000-04-12', moment().subtract(1, 'd').format('YYYY-MM-DD')]
                ],
                select: function(date, context) {
                    if (date[0]) {
                        // Pignose moment objects can be tricky, format them to strings for comparison
                        let selected = date[0].format('YYYY-MM-DD');

                        $('.selected_date').text('Book on ' + date[0].toDate().toDateString());

                        if (selected === todayStr) {
                            initTime(); // Today logic
                        } else {
                            initTime(selected); // Future logic
                        }

                        // Sync with Livewire/Backend
                        @this.set('bookDate', selected);
                    }
                }
            });
        });

         $('body').on('click', '.selected_time', function () {
            $('#contact_container').removeClass('hidden');
            setTimeout(() => {
                $('#contactname').focus();
            }, 100)
            $('#appointment #date').text($('.selected_date').text() + ' at' + ' ' + $(this).text())
            $('#book_time').val($(this).text());
            $('#calendar_container').addClass('hidden')
            @this.set('bookTime', $(this).text());
        })

        $('#contact_container a').click( function (e) {
            e.preventDefault()
            $('#contact_container').addClass('hidden');
            $('#calendar_container').removeClass('hidden');
        })

        // The updated, clean initTime function
        function initTime(param) {
            $('.time_selection').empty();
            let i = 0;
            let currentTime;

            if (param) {
                // Future logic: Start at 10:00 AM
                currentTime = moment(param + ' 10:00:00', 'YYYY-MM-DD HH:mm:ss');
            } else {
                // Today logic: Start from right now
                currentTime = moment();
            }

            // Logic: While we are before 5:00 PM
            while (currentTime.hour() < 17) {
                // Round to next 30-min block
                let mins = currentTime.minutes();
                if (mins < 30) {
                    currentTime.minutes(30).seconds(0);
                } else {
                    currentTime.add(1, 'hour').minutes(0).seconds(0);
                }

                // Stop if rounding pushed us past 5 PM
                if (currentTime.hour() > 17 || (currentTime.hour() === 17 && currentTime.minutes() > 0)) {
                    break;
                }

                let timeLabel = currentTime.format('h:mm a');

                $('<a>', {
                    id: 'selected_time' + i,
                    class: 'selected_time',
                    text: timeLabel
                }).appendTo('.time_selection');

                i++;

                // Safety break for exactly 5 PM
                if (currentTime.hour() === 17) break;
            }
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('calendar-close-modal', (event) => {
                const closeButton = document.getElementById('calendar-close-button');
                if (closeButton) {

                    @this.set('productId', {{$productId}});
                    $('div[id$="_error"]').each(function() {
                        $(this).remove();
                    });
                    closeButton.click();
                    $('#contact_container a')[0].click();
                    setTimeout(() => {
                        alert ('Your email will be sent to an appropriate department. You will be contacted soon. Thank you.')
                    },100)
                }
            });

            Livewire.on('show-validation-errors', (errors) => {
                // Use Object.values to get all error messages and loop through them
                Object.values(errors).forEach(errorMessagesArray => {
                    const keys = Object.keys(errorMessagesArray);
                    const values = Object.values(errorMessagesArray);
                    let i = 0;

                    values.forEach(errorMessage => {
                        // Display each error message
                        const errorMessageElement = document.createElement('div');
                        errorMessageElement.classList.add('text-red-500');
                        let fieldName = keys[i];
                        let shortFieldName = fieldName.split('.').pop(); // 'email' or 'contact_name'
                        if ($('#'+shortFieldName+'_error').length > 0)
                            $('#'+shortFieldName+'_error').remove();

                        errorMessageElement.id = shortFieldName+'_error';
                        errorMessageElement.textContent = errorMessage;

                        i ++;
                        $('#'+shortFieldName).after(errorMessageElement)
                    });

                });
            });
        });

    </script>
</div>