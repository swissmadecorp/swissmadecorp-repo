<div class="sw-watch-root" x-data="{ uploading: false, progress: 0 }"
    x-on:livewire-upload-start="uploading = true; progress = 0"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-error="uploading = false"
    x-on:livewire-upload-cancel="uploading = false">
    <style>
        .sw-sell-page { --sw-red: #d7192d; --sw-ink: #17202b; --sw-muted: #697386; --sw-line: #e2e6ec; background: #fff; color: var(--sw-ink); padding: 48px 24px 64px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.5; }
        .sw-sell-page *, .sw-sell-page *::before, .sw-sell-page *::after { box-sizing: border-box; }
        .sw-sell-page [x-cloak] { display: none !important; }
        .sw-sell-page .sw-shell { width: 100%; margin: 0; }
        .sw-watch-root .sw-sell-page { padding-left: 0; padding-right: 0; }
        .sw-sell-page .sw-heading { margin-bottom: 28px; }
        .sw-sell-page .sw-success h1 { margin: 0 0 10px; font-size: 32px; font-weight: 700; line-height: 1.15; }
        .sw-sell-page .sw-intro { max-width: 690px; margin: 0; color: var(--sw-muted); font-size: 16px; line-height: 1.65; }
        .sw-sell-page .sw-layout { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(300px, 1fr); gap: 24px; align-items: start; margin: 0; }
        .sw-sell-page .sw-main { display: grid; gap: 20px; min-width: 0; }
        .sw-sell-page .sw-card { min-width: 0; padding: 26px; border: 1px solid var(--sw-line); border-radius: 14px; background: #fff; box-shadow: 0 3px 12px rgba(23,32,43,.025); }
        .sw-sell-page h2 { margin: 0 0 22px; font-size: 21px; font-weight: 700; line-height: 1.3; letter-spacing: -.5px; }
        .sw-sell-page .sw-fields { display: grid; gap: 18px; }
        .sw-sell-page .sw-two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sw-sell-page .sw-label { display: block; margin: 0 0 7px; color: var(--sw-ink); font-size: 13px; font-weight: 600; }
        .sw-sell-page .sw-input { display: block; width: 100%; min-width: 0; height: 44px; margin: 0; padding: 10px 12px; border: 1px solid #d8dee7; border-radius: 8px; background: #fff; color: var(--sw-ink); font: inherit; box-shadow: none; outline: none; transition: border-color .15s, box-shadow .15s; }
        .sw-sell-page .sw-input::placeholder { color: #929bab; opacity: 1; }
        .sw-sell-page .sw-input:focus { border-color: var(--sw-red); box-shadow: 0 0 0 3px rgba(215,25,45,.08); }
        .sw-sell-page textarea.sw-input { height: auto; min-height: 100px; resize: vertical; }
        .sw-sell-page .sw-choice-input { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
        .sw-sell-page .sw-intent-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 22px; }
        .sw-sell-page .sw-intent { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; min-height: 138px; margin: 0; padding: 18px 12px; border: 1px solid #d8dee7; border-radius: 10px; background: #fff; text-align: center; cursor: pointer; transition: border-color .15s, background .15s; }
        .sw-sell-page .sw-intent svg { width: 30px; height: 30px; margin-bottom: 3px; }
        .sw-sell-page .sw-intent strong { font-size: 17px; font-weight: 600; }
        .sw-sell-page .sw-intent small { max-width: 180px; color: var(--sw-muted); font-size: 12px; font-weight: 400; line-height: 1.5; }
        .sw-sell-page .sw-intent::after { content: '\2713'; position: absolute; top: 10px; right: 10px; display: grid; place-items: center; width: 20px; height: 20px; border-radius: 50%; background: var(--sw-red); color: white; font-size: 12px; opacity: 0; }
        .sw-sell-page .sw-choice-input:checked + .sw-intent { border-color: var(--sw-red); background: #fff7f8; }
        .sw-sell-page .sw-choice-input:checked + .sw-intent svg { color: var(--sw-red); }
        .sw-sell-page .sw-choice-input:checked + .sw-intent::after { opacity: 1; }
        .sw-sell-page .sw-choice-input:focus-visible + label { outline: 3px solid rgba(215,25,45,.35); outline-offset: 3px; }
        .sw-sell-page .sw-intent:hover { border-color: var(--sw-red); }
        .sw-sell-page fieldset { min-width: 0; margin: 0; padding: 0; border: 0; }
        .sw-sell-page .sw-question { display: grid; grid-template-columns: minmax(0, 1fr) 182px; gap: 16px; align-items: center; padding: 10px 0; }
        .sw-sell-page .sw-question-text { margin: 0; font-weight: 400; }
        .sw-sell-page .sw-segments { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
        .sw-sell-page .sw-segment, .sw-sell-page .sw-age-pill { display: block; margin: 0; padding: 9px 12px; border: 1px solid transparent; border-radius: 8px; background: #f0f2f5; color: #576172; font-size: 12px; font-weight: 400; text-align: center; cursor: pointer; transition: background .15s, color .15s; }
        .sw-sell-page .sw-choice-input:checked + .sw-segment, .sw-sell-page .sw-choice-input:checked + .sw-age-pill { background: var(--sw-ink); color: #fff; }
        .sw-sell-page .sw-segment:hover, .sw-sell-page .sw-age-pill:hover { background: #e4e8ed; }
        .sw-sell-page .sw-detail-field { margin-top: 22px; }
        .sw-sell-page .sw-age-options { display: flex; flex-wrap: wrap; gap: 6px; }
        .sw-sell-page .sw-age-pill { padding: 9px 10px; font-size: 11px; }
        .sw-sell-page .sw-button { display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%; min-height: 46px; padding: 12px 18px; border: 0; border-radius: 9px; background: var(--sw-red); color: #fff; font: inherit; font-weight: 600; text-decoration: none; cursor: pointer; transition: background .15s; }
        .sw-sell-page .sw-button:hover { background: #b81325; color: #fff; }
        .sw-sell-page .sw-button:focus-visible, .sw-sell-page .sw-remove:focus-visible { outline: 3px solid #f4a0aa; outline-offset: 3px; }
        .sw-sell-page button:disabled { opacity: .55; cursor: wait; }
        .sw-sell-page .sw-submit { margin-top: 24px; }
        .sw-sell-page .sw-button svg { width: 19px; height: 19px; flex-shrink: 0; }
        .sw-sell-page .sw-photo-card { position: sticky; top: 150px; }
        .sw-sell-page .sw-gallery { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; min-height: 208px; margin-top: 18px; padding: 10px; border: 1px solid var(--sw-line); border-radius: 10px; background: #f7f8fa; }
        .sw-sell-page .sw-photo { position: relative; min-width: 0; aspect-ratio: 1 / 1.15; overflow: hidden; border: 1px solid var(--sw-line); border-radius: 8px; background: #fff; }
        .sw-sell-page .sw-photo img { display: block; width: 100%; height: 100%; object-fit: contain; }
        .sw-sell-page .sw-remove { position: absolute; bottom: 7px; left: 7px; display: flex; align-items: center; gap: 5px; margin: 0; padding: 5px 8px; border: 1px solid #e2e6ec; border-radius: 20px; background: rgba(255,255,255,.96); color: #17202b; font: inherit; font-size: 11px; cursor: pointer; }
        .sw-sell-page .sw-remove:hover { color: var(--sw-red); }
        .sw-sell-page .sw-empty-photo { display: grid; place-items: center; min-height: 180px; border: 1px dashed #d6dce5; border-radius: 8px; background: #f0f2f5; color: #a4adba; }
        .sw-sell-page .sw-empty-photo svg { width: 30px; height: 30px; }
        .sw-sell-page .sw-photo-hint { margin: 16px 0 0; color: var(--sw-muted); font-size: 12px; line-height: 1.6; }
        .sw-sell-page .sw-status { margin: 12px 0 0; color: var(--sw-muted); font-size: 12px; }
        .sw-sell-page .sw-error { display: block; margin: 6px 0 0; color: #b81325; font-size: 12px; }
        .sw-sell-page .sw-success { max-width: 660px; margin: 35px auto; padding: 44px; text-align: center; }
        .sw-sell-page .sw-success .sw-button { width: auto; margin-top: 24px; }
        @media (max-width: 820px) { .sw-sell-page { padding: 32px 18px 44px; } .sw-sell-page .sw-layout { grid-template-columns: minmax(0, 1fr); gap: 20px; } .sw-sell-page .sw-main { display: contents; } .sw-sell-page .sw-photo-card { position: static; order: 1; } .sw-sell-page .sw-final-card { order: 2; } .sw-sell-page .sw-photo { aspect-ratio: 1; } }
        @media (max-width: 480px) { .sw-sell-page { padding: 28px 12px 36px; } .sw-sell-page .sw-card { padding: 20px; border-radius: 12px; } .sw-sell-page .sw-two { grid-template-columns: minmax(0, 1fr); } .sw-sell-page .sw-question { grid-template-columns: minmax(0, 1fr) 126px; gap: 10px; font-size: 12px; } .sw-sell-page .sw-segment { padding: 9px 8px; } .sw-sell-page .sw-intent { min-height: 136px; padding: 18px 8px; } .sw-sell-page .sw-intent small { font-size: 11px; } }
    </style>

    <div class="flex justify-center">
        <div class="container p-5">
            @if(!request()->query('saved'))
                <h1 class="text-3xl uppercase pb-2">Sell your watch</h1>
                <hr>
            @endif

    <div class="sw-sell-page">
    <div class="sw-shell">
        @if(!request()->query('saved'))
            <header class="sw-heading">
                <p class="sw-intro">Sell or trade in your luxury watch. Share a few details and photos below, and our team will be in touch.</p>
            </header>

            <form wire:submit="save" class="sw-layout">
                <div class="sw-main">
                    <section class="sw-card" aria-labelledby="sw-your-details">
                        <h2 id="sw-your-details">Your details</h2>
                        <div class="sw-fields">
                            @foreach ([['name', 'Name', 'text', 'Your full name', 'name'], ['email', 'Email', 'email', 'your@email.com', 'email'], ['phone', 'Phone', 'tel', '+1 (555) 123-4567', 'tel']] as [$field, $label, $type, $placeholder, $autocomplete])
                                <div>
                                    <label for="sw-{{ $field }}" class="sw-label">{{ $label }}</label>
                                    <input id="sw-{{ $field }}" wire:model="contact.{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" placeholder="{{ $placeholder }}" class="sw-input" aria-required="true" @error('contact.'.$field) aria-invalid="true" aria-describedby="sw-error-{{ $field }}" @enderror>
                                    @error('contact.'.$field)<span id="sw-error-{{ $field }}" class="sw-error" role="alert">{{ $message }}</span>@enderror
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="sw-card" aria-labelledby="sw-your-watch">
                        <h2 id="sw-your-watch">Your watch</h2>
                        <fieldset>
                            <legend class="sw-label">I would like to</legend>
                            <div class="sw-intent-grid">
                                <div>
                                    <input class="sw-choice-input" type="radio" id="sw-sell" name="sw-intent" wire:model="contact.selltrade.1" value="sell-yes">
                                    <label class="sw-intent" for="sw-sell">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M20 4h-7L3 14l7 7L20 11V4Z" stroke-linejoin="round"/><circle cx="16" cy="8" r="1"/></svg>
                                        <strong>Sell</strong><small>Get an offer for your watch</small>
                                    </label>
                                </div>
                                <div>
                                    <input class="sw-choice-input" type="radio" id="sw-trade" name="sw-intent" wire:model="contact.selltrade.1" value="sell-no">
                                    <label class="sw-intent" for="sw-trade">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 7h16m-4-4 4 4-4 4M20 17H4m4-4-4 4 4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <strong>Trade in</strong><small>Use the value towards a new watch</small>
                                    </label>
                                </div>
                            </div>
                            @error('contact.selltrade.1')<span class="sw-error" role="alert">{{ $message }}</span>@enderror
                        </fieldset>
                        <div class="sw-fields sw-two">
                            @foreach ([['brand', 'Brand', 'e.g. Rolex'], ['model', 'Model / reference', 'e.g. Submariner 126610LN']] as [$field, $label, $placeholder])
                                <div>
                                    <label for="sw-{{ $field }}" class="sw-label">{{ $label }}</label>
                                    <input id="sw-{{ $field }}" wire:model="contact.{{ $field }}" type="text" class="sw-input" placeholder="{{ $placeholder }}" aria-required="true" @error('contact.'.$field) aria-invalid="true" aria-describedby="sw-error-{{ $field }}" @enderror>
                                    @error('contact.'.$field)<span id="sw-error-{{ $field }}" class="sw-error" role="alert">{{ $message }}</span>@enderror
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="sw-card sw-final-card" aria-labelledby="sw-final-details">
                        <h2 id="sw-final-details">A few final details</h2>
                        @foreach ([['boxpapers', 'box', 'Original box'], ['cert', 'cert', 'Certificate or warranty card'], ['purchased', 'purchased', 'Purchased from Swiss Made Corp'], ['proof', 'proof', 'Proof of purchase'], ['unworn', 'unworn', 'Unworn']] as [$field, $prefix, $question])
                            <div role="group" aria-labelledby="sw-question-{{ $field }}">
                                <div class="sw-question">
                                    <span class="sw-question-text" id="sw-question-{{ $field }}">{{ $question }}</span>
                                    <div class="sw-segments">
                                        @foreach (['yes' => 'Yes', 'no' => 'No'] as $value => $label)
                                            <div>
                                                <input type="radio" class="sw-choice-input" name="sw-{{ $field }}" id="sw-{{ $field }}-{{ $value }}" wire:model="contact.{{ $field }}.1" value="{{ $prefix }}-{{ $value }}">
                                                <label for="sw-{{ $field }}-{{ $value }}" class="sw-segment">{{ $label }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('contact.'.$field.'.1')<span class="sw-error" role="alert">{{ $message }}</span>@enderror
                            </div>
                        @endforeach

                        <fieldset class="sw-detail-field">
                            <legend class="sw-label">Watch age</legend>
                            <div class="sw-age-options">
                                @foreach (['Less than 2 years', '2 to 3 years', '3 to 5 years', '5 to 10 years', 'Over 10 years'] as $age)
                                    <div>
                                        <input class="sw-choice-input" type="radio" name="sw-age" id="sw-age-{{ $loop->index }}" wire:model="contact.age" value="{{ $age }}">
                                        <label class="sw-age-pill" for="sw-age-{{ $loop->index }}">{{ $age }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                        <div class="sw-detail-field">
                            <label for="sw-amount" class="sw-label">Desired amount (USD)</label>
                            <input type="text" inputmode="decimal" id="sw-amount" wire:model="contact.amount" placeholder="e.g. 12,500" class="sw-input" aria-required="true" @error('contact.amount') aria-invalid="true" aria-describedby="sw-error-amount" @enderror>
                            @error('contact.amount')<span id="sw-error-amount" class="sw-error" role="alert">{{ $message }}</span>@enderror
                        </div>
                        <div class="sw-detail-field">
                            <label for="sw-comment" class="sw-label">Additional details <span style="font-weight: 400; color: #697386;">(optional)</span></label>
                            <textarea id="sw-comment" wire:model="contact.comment" class="sw-input" rows="4" placeholder="Condition, service history, or anything else you'd like to share..."></textarea>
                        </div>
                        <button type="submit" wire:loading.attr="disabled" x-bind:disabled="uploading" class="sw-button sw-submit">
                            <span wire:loading.remove wire:target="save">Submit your request</span>
                            <span wire:loading wire:target="save">Submitting your request...</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 12h16m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </section>
                </div>

                <aside class="sw-card sw-photo-card" aria-labelledby="sw-photos-title">
                    <h2 id="sw-photos-title">Photos of your watch</h2>
                    <button type="button" x-on:click="$refs.watchPhotos.click()" wire:loading.attr="disabled" x-bind:disabled="uploading" class="sw-button">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8" cy="8" r="1.5"/><path d="m3 17 5-5 4 4 4-6 5 7" stroke-linejoin="round"/></svg>
                        Choose Files
                    </button>
                    <input x-ref="watchPhotos" wire:key="watch-photo-input-{{ $imageInputVersion }}" wire:model="pendingImages" wire:loading.attr="disabled" x-bind:disabled="uploading" style="display: none;" aria-label="Choose watch photos" accept="image/png, image/jpeg" type="file" multiple>
                    <p x-show="uploading" x-cloak class="sw-status" role="status">Preparing previews... <span x-text="progress + '%'"></span></p>
                    <div class="sw-gallery" id="image-container" role="group" aria-label="Selected watch photos">
                        @foreach ($images as $index => $image)
                            <div class="sw-photo" wire:key="watch-photo-{{ $image->getFilename() }}">
                                <img src="{{ $image->temporaryUrl() }}" alt="Watch photo {{ $loop->iteration }}">
                                <button type="button" wire:click="removeImage({{ $index }})" wire:loading.attr="disabled" x-bind:disabled="uploading" aria-label="Remove photo {{ $loop->iteration }}" class="sw-remove"><span aria-hidden="true">&times;</span> Remove</button>
                            </div>
                        @endforeach
                        @for ($slot = 0; $slot < max(0, 4 - count($images)); $slot++)
                            <div class="sw-empty-photo" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M7 12h10"/></svg></div>
                        @endfor
                    </div>
                    <p class="sw-photo-hint">5–10 JPEG or PNG photos, up to 5 MB each.</p>
                    <p class="sw-photo-hint">Include the front, back, sides, and any accessories. You can add more photos or remove them before submitting.</p>
                    @foreach (['images', 'images.*', 'pendingImages', 'pendingImages.*'] as $photoError)
                        @foreach ($errors->get($photoError) as $messages)
                            @foreach ((array) $messages as $message)
                                <span class="sw-error" role="alert">{{ $message }}</span>
                            @endforeach
                        @endforeach
                    @endforeach
                </aside>
            </form>
        @else
            <div class="sw-card sw-success">
                <h1>Thank you</h1>
                <p class="sw-intro">Your request has been submitted. Our team will contact you soon. In the meantime, explore our watch collection.</p>
                <a href="{{ url('watch-products') }}" class="sw-button">Browse our watches <span aria-hidden="true">&rarr;</span></a>
            </div>
        @endif
    </div>
    </div>
        </div>
    </div>
</div>
