<div>
    <button id="default-modal-trigger" data-modal-target="default-modal" data-modal-toggle="default-modal" class="hidden" type="button">Send Message</button>

    <!-- Main modal -->
    <div wire:ignore.self id="default-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div id="modal-box" class="relative p-4 w-full max-w-2xl max-h-full transition-all duration-300">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Send Message - {{ !empty($selectedUser) && isset($selectedUser->name) ? $selectedUser->name : $selectedUser}}
                    </h3>
                    <button type="button" class="closeModal text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="default-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>

                </div>
                <!-- Modal body -->
<div>
    <div class="space-y-4">
        <div class="flex">
            <select wire:model.lazy="selectedUserId" id="users" size="5" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block w-44 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                {{-- <option value="0">Group Text</option> --}}
                @foreach ($loggedInUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->username }}</option>
                @endforeach
            </select>
            <div class="w-full p-1.5">
                {{-- IMPORTANT: This is the main messages container with x-init for IntersectionObserver --}}
                <div

                    class="text-xs w-full h-[340px] overflow-y-auto"
                    id="messagesBody">

                    @foreach ($messagesObj as $msg)
                        {{-- IMPORTANT: Add message-item class and data attributes here --}}
                        <div
                            class="message-item p-1 {{ $msg->sender_id == auth()->id() ? 'text-right' : 'text-left' }}"
                            data-message-id="{{ $msg->id }}"
                            data-sender-id="{{ $msg->sender_id }}" {{-- This should be the sender's ID of this message --}}
                            data-seen="{{ $msg->seen ? '1' : '0' }}"
                            data-is-incoming="{{ $msg->sender_id != auth()->id() ? 'true' : 'false' }}"
                        >
                            <div class="rounded-md text-left inline-block my-1 p-2 rounded {{ $msg->sender_id == auth()->id() ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                                <div class="mr-1 ml-1">
                                    {{-- Removed problematic '`m,`' from target="_blank" --}}
                                    @if (filter_var($msg->message, FILTER_VALIDATE_URL))
                                        <a target="_blank" href="{{ $msg->message }}">{{ $msg->message }}</a>
                                    @else
                                        {{ $msg->message }}
                                    @endif
                                </div>

                                @if (!empty($msg->image_path) && file_exists(public_path($msg->image_path)))
                                    @php
                                        $path = public_path($msg->image_path);
                                        $exists = !empty($msg->image_path) && file_exists($path);
                                        $mime = $exists ? mime_content_type($path) : null;

                                        $isImage = $mime && Str::startsWith($mime, 'image/');
                                        $isPdf   = $mime === 'application/pdf';
                                        $isDoc   = in_array($mime, [
                                            'application/msword',
                                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                            'application/vnd.ms-excel',
                                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                                        ]); // NEW

                                        $fallback = 'data:image/gif;base64,R0lGODlhAQABAAAAACwAAAAAAQABAAA=';
                                    @endphp

                                    <div x-data="{ showPreview: false }"
                                        @keydown.escape.window="showPreview = false"
                                        class="bg-white mt-2 p-1 rounded-lg">

                                        {{-- Preview Trigger --}}
                                        @if ($isImage)
                                            <img
                                                src="{{ asset($msg->image_path) }}"
                                                @click="showPreview = true"
                                                class="cursor-pointer rounded shadow max-w-[10rem] transition-transform duration-200 hover:scale-103"
                                                alt="Image"
                                            />
                                        @elseif ($isPdf)
                                            <div @click="showPreview = true" class="cursor-pointer text-blue-600 underline">
                                                <svg viewBox="-4 0 40 40" fill="white" class="bg-white p-1.5 rounded-lg" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                                <g id="SVGRepo_iconCarrier">
                                                    <path d="M25.6686 26.0962C25.1812 26.2401 24.4656 26.2563 23.6984 26.145C22.8750 26.0256 22.0351 25.7739 21.2096 25.4030C22.6817 25.1888 23.8237 25.2548 24.8005 25.6009C25.0319 25.6829 25.4120 25.9021 25.6686 26.0962ZM17.4552 24.7459C17.3953 24.7622 17.3363 24.7776 17.2776 24.7939C16.8815 24.9017 16.4961 25.0069 16.1247 25.1005L15.6239 25.2275C14.6165 25.4824 13.5865 25.7428 12.5692 26.0529C12.9558 25.1206 13.3150 24.1780 13.6667 23.2564C13.9271 22.5742 14.1930 21.8773 14.4680 21.1894C14.6075 21.4198 14.7531 21.6503 14.9046 21.8814C15.5948 22.9326 16.4624 23.9045 17.4552 24.7459ZM14.8927 14.2326C14.9580 15.3830 14.7098 16.4897 14.3457 17.5514C13.8972 16.2386 13.6882 14.7889 14.2489 13.6185C14.3927 13.3185 14.5105 13.1581 14.5869 13.0744C14.7049 13.2566 14.8601 13.6642 14.8927 14.2326ZM9.63347 28.8054C9.38148 29.2562 9.12426 29.6782 8.86063 30.0767C8.22442 31.0355 7.18393 32.0621 6.64941 32.0621C6.59681 32.0621 6.53316 32.0536 6.44015 31.9554C6.38028 31.8926 6.37069 31.8476 6.37359 31.7862C6.39161 31.4337 6.85867 30.8059 7.53527 30.2238C8.14939 29.6957 8.84352 29.2262 9.63347 28.8054ZM27.3706 26.1461C27.2889 24.9719 25.3123 24.2186 25.2928 24.2116C24.5287 23.9407 23.6986 23.8091 22.7552 23.8091C21.7453 23.8091 20.6565 23.9552 19.2582 24.2819C18.0140 23.3999 16.9392 22.2957 16.1362 21.0733C15.7816 20.5332 15.4628 19.9941 15.1849 19.4675C15.8633 17.8454 16.4742 16.1013 16.3632 14.1479C16.2737 12.5816 15.5674 11.5295 14.6069 11.5295C13.9480 11.5295 13.3807 12.0175 12.9194 12.9813C12.0965 14.6987 12.3128 16.8962 13.5620 19.5184C13.1121 20.5751 12.6941 21.6706 12.2895 22.7311C11.7861 24.0498 11.2674 25.4103 10.6828 26.7045C9.04334 27.3532 7.69648 28.1399 6.57402 29.1057C5.83870 29.7373 4.95223 30.7028 4.90163 31.7107C4.87693 32.1854 5.03969 32.6207 5.37044 32.9695C5.72183 33.3398 6.16329 33.5348 6.64870 33.5354C8.25189 33.5354 9.79489 31.3327 10.0876 30.8909C10.6767 30.0029 11.2281 29.0124 11.7684 27.8699C13.1292 27.3781 14.5794 27.0110 15.9850 26.6562L16.4884 26.5283C16.8668 26.4321 17.2601 26.3257 17.6635 26.2153C18.0904 26.0999 18.5296 25.9802 18.9760 25.8665C20.4193 26.7844 21.9714 27.3831 23.4851 27.6028C24.7601 27.7883 25.8924 27.6807 26.6589 27.2811C27.3486 26.9219 27.3866 26.3676 27.3706 26.1461ZM30.4755 36.2428C30.4755 38.3932 28.5802 38.5258 28.1978 38.5301H3.74486C1.60224 38.5301 1.47322 36.6218 1.46913 36.2428L1.46884 3.75642C1.46884 1.60390 3.36763 1.47340 3.74457 1.46908H20.2630L20.2718 1.47780V7.92396C20.2718 9.21763 21.0539 11.6669 24.0158 11.6669H30.4203L30.4753 11.7218L30.4755 36.2428ZM28.9572 10.1976H24.0169C21.8749 10.1976 21.7453 8.29969 21.7424 7.92417V2.95307L28.9572 10.1976ZM31.9447 36.2428V11.1157L21.7424 0.871022V0.823357H21.6936L20.8742 0H3.74491C2.44954 0 0 0.785336 0 3.75711V36.2435C0 37.5427 0.782956 40 3.74491 40H28.2001C29.4952 39.9997 31.9447 39.2143 31.9447 36.2428Z" fill="#EB5757"></path>
                                                </g>
                                            </svg>
                                                click to preview PDF
                                            </div>
                                        @elseif ($isDoc) {{-- NEW --}}
                                            {{-- Add a download link for reliability --}}
                                            <a href="{{ asset($msg->image_path) }}" class="text-xs text-gray-500 hover:underline mt-1 block" download>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                    <path d="M14 2v6h6"></path>
                                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                                    <line x1="10" y1="9" x2="10" y2="9"></line>
                                                </svg>
                                                download file
                                            </a>
                                        @else
                                            <img src="{{ $fallback }}" class="rounded shadow max-w-[10rem]" />
                                        @endif

                                        {{-- Fullscreen Modal --}}
                                        <div
                                            x-show="showPreview"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                            @click.outside="showPreview = false"
                                            x-cloak
                                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4"
                                        >
                                            <!-- ✖ Close Button -->
                                            <button
                                                @click="showPreview = false"
                                                class="absolute top-2 right-2 bg-gray-50 hover:bg-gray-200 text-gray-800 p-2 rounded-full z-50"
                                                aria-label="Close"
                                            >
                                                ✖
                                            </button>

                                            <div class="relative bg-white rounded shadow-lg overflow-hidden max-w-5xl w-auto p-4">
                                                @if ($isImage)
                                                    <img src="{{ asset($msg->image_path) }}" class="max-w-full max-h-full rounded shadow-lg" />
                                                @elseif ($isPdf)
                                                    <iframe src="{{ asset($msg->image_path) }}" class="w-[80vw] h-[90vh] border-0 rounded shadow-lg"></iframe>
                                                @elseif ($isDoc) {{-- NEW --}}
                                                    <iframe src="https://docs.google.com/gview?url={{ urlencode(asset($msg->image_path)) }}&embedded=true"
                                                            class="w-[80vw] h-[90vh] border-0 rounded shadow-lg"></iframe>
                                                @else
                                                    <p class="text-gray-600">This file type cannot be previewed. <a href="{{ asset($msg->image_path) }}" class="underline text-blue-600">Download</a></p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                </div><br>
                                <div class="flex items-center {{ $msg->sender_id == auth()->id() ? 'justify-end' : '' }} ml-1 text-xs">
                                    {{$msg->created_at->format('m-d-Y')}}
                                    @if ($msg->sender_id === auth()->id())
                                        <div>
                                            @if ($msg->seen)
                                                <svg class="inline w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 10-1.414 1.414l4.5 4.5a1 1 0 001.414 0l7.5-7.5a1 1 0 000-1.414z"/>
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="typing-indicator" class="pl-6 text-gray-400 text-xs mt-1 mb-1 h-4 transition-opacity duration-200"></div>

                    <div wire:ignore id="pasted-image-preview" class="bg-gray-50 flex justify-center my-2 p-5 rounded-full hidden">
                        <img id="pasted-image" src="" class="max-w-[xs] max-h-40 rounded shadow-md" />
                        <button id="remove-pasted-image" class="ml-2 text-sm text-red-500 underline">Remove</button>
                    </div>
                    <input type="file" id="pastedImageInput" wire:model="imageFile" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="hidden" />
                    <div class="relative bg-gray-50 border-2 border-blue-300 flex pl-2 rounded-full"
                        x-data="{ textInput: @entangle('textMessage').live }">
                        <button id="fileopen" class="text-2xl cursor-pointer mb-1">+</button>
                        <textarea
                            x-model="textInput"
                            x-on:keydown.enter.prevent="textInput.trim().length > 0 && $wire.submitText()"
                            id="textmessage"
                            class="bg-gray-50 border-0 field-sizing-content focus:outline-none focus:ring-0 max-h-[150px] p-2.5 pl-6 resize-none rounded-full text-sm w-full"
                            placeholder="Type your message"
                            required
                        ></textarea>

                        <div class="flex items-center">
                            <button
                                id="modalForm"
                                wire:click="submitText()"
                                type="button"
                                class="!filter bg-blue-700 border-2 dark:bg-blue-600 dark:focus:ring-blue-800 dark:hover:bg-blue-700 focus:outline-none focus:ring-blue-300 font-medium hover:bg-blue-800 items-center justify-center p-2 px-2 rounded-full text-center text-sm text-white"
                                x-show="textInput.trim().length > 0"
                                x-bind:disabled="textInput.trim().length === 0"
                                x-transition
                                x-cloak {{-- Add x-cloak here --}}
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2 3h20v14H6l-4 4V3zm2 2v10.586L6.586 13H20V5H4zm2 3h12v2H6V8z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                <!-- End Modal body -->
            </div>
        </div>
    </div>

@script
   <script>

    $(document).ready( function() {
        let originalTitle = document.title;
        let blinkInterval = null;
        let typingTimeout = null;

        document.addEventListener('paste', function (event) {
            const items = (event.clipboardData || event.originalEvent.clipboardData).items;

            for (let i = 0; i < items.length; i++) {
                const item = items[i];

                if (item.type.indexOf('image') === 0) {
                    const blob = item.getAsFile();
                    const file = new File([blob], 'pasted.png', { type: blob.type });

                    const dt = new DataTransfer();
                    dt.items.add(file);

                    const input = document.getElementById('pastedImageInput');
                    input.files = dt.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));

                    // Optional: preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        debugger
                        $('#pasted-image').attr('src', e.target.result);
                        $('#pasted-image-preview').removeClass('hidden');
                    };

                    reader.readAsDataURL(file);
                }
            }

            // document.getElementById('modalForm').click();
        });

        $('#remove-pasted-image').on('click', function () {
            $('#pastedImageInput').val('');
            $('#pasted-image-preview').addClass('hidden');
            $('#pasted-image').attr('src', '');
            $wire.$set('imageFile',null)
        });

        function startTabBlink(message = '🔔 New Message!') {
            if (document.hasFocus()) return; // Only blink if tab is NOT active

            if (blinkInterval) return; // Prevent multiple intervals

            blinkInterval = setInterval(() => {
                document.title = document.title === message ? originalTitle : message;
            }, 1000);
        }

        function stopTabBlink() {
            if (blinkInterval) {
                clearInterval(blinkInterval);
                blinkInterval = null;
                document.title = originalTitle;
            }
        }

        // Reset blinking when the user comes back
        // window.addEventListener('focus', stopTabBlink);
        $(window).on('focus', function() {
            // Actions to perform when the window gains focus
            $wire.$call('markMessagesAsRead');
            stopTabBlink();
        });

        $('.closeModal').click(function() {
            const modalElement = document.getElementById('default-modal');

            if (document.activeElement) {
                document.activeElement.blur();
            }
            modalElement.click();
            // const modal = new Modal(modalElement);
            // modal.hide();
        })

        $wire.on('imageSelected', event => {
            if (event.ext != 'pdf')
                $('#pasted-image').attr('src', event.src);

            $('#pasted-image-preview').removeClass('hidden');
        })

        $wire.on('userTyping', event => {
            window.Echo.private(`message.${event.selectedUserId}`).whisper('typing', {
                userId: event.userId,
                userName: event.userName,
            });
        })

        window.Echo.private(`message.{{$loginId}}`).listenForWhisper('typing', (event) => {
            var t = $('#typing-indicator')
            t.text(`${event.userName} is typing ...`)

            // Reset any existing timeout
            if (typingTimeout) {
                clearTimeout(typingTimeout);
            }

            // Start a new timeout — only hide after 2 seconds of no whispers
            typingTimeout = setTimeout(() => {
                t.text('');
                typingTimeout = null;
            }, 2000);
        })

        $('#fileopen').click(function() {
            $('#pastedImageInput').click();
        })

        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            // Equivalent of 'message.sent'

            succeed(({ snapshot, effect }) => {
                // Equivalent of 'message.received'
            })
            fail(() => {
                // Equivalent of 'message.failed'
            })
        })

        scrollToBottom = () => {
            const scrollableDiv = $('#messagesBody');
            if (scrollableDiv.length) {
                scrollableDiv.scrollTop(scrollableDiv[0].scrollHeight);
            }
        }

        // Observe the modal for class changes
        const modal = document.getElementById('default-modal');

        if (modal) {
            const observer = new MutationObserver((mutationsList) => {
                for (const mutation of mutationsList) {
                    if (mutation.attributeName === 'class') {
                        const isVisible = !modal.classList.contains('hidden');
                        if (isVisible) {
                            // Modal just opened → dispatch scroll event

                            $wire.$call('markMessagesAsRead')
                            scrollToBottom();
                        }
                    }
                }
            });

            observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
        }

        $wire.on('get-message', msg => {
            const modalWasHidden = $('#default-modal').hasClass('hidden');

            // ✅ Step 1: Trigger modal if it's hidden
            if (modalWasHidden) {
                setTimeout(() => {
                    $('#default-modal-trigger').click();
                }, 300);
            }

            // ✅ Step 2: Scroll + focus after modal open
            setTimeout(() => {
                scrollToBottom();

                $('#textmessage').focus();
                $('#pastedImageInput').val('');
                $('#pasted-image-preview').addClass('hidden');
                $('#pasted-image').attr('src', '');
            }, 500); // delay to ensure modal is rendered

            // ✅ Step 3: Start blinking — but only if tab is inactive
            if (!document.hasFocus()) {
                setTimeout(() => {
                    startTabBlink('🔔 New Message!');
                }, 1000); // run it last
            }

        });
    })

</script>
@endscript

</div>
