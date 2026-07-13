@props(['action' => 'submit'])
@if (recaptcha()->enabled())
    @php $recaptchaUid = 'grc_'.\Illuminate\Support\Str::random(8); @endphp
    <input type="hidden" name="g-recaptcha-response" id="{{ $recaptchaUid }}">
    <script src="https://www.google.com/recaptcha/api.js?render={{ recaptcha()->siteKey() }}"></script>
    <script>
        (function () {
            var input = document.getElementById(@json($recaptchaUid));
            var form = input.closest('form');
            if (! form) return;

            var siteKey = @json(recaptcha()->siteKey());
            var action = @json($action);
            var submitting = false;

            form.addEventListener('submit', function (event) {
                // Always intercept: fetch a fresh v3 token, then submit natively.
                event.preventDefault();
                if (submitting) return;
                if (! form.reportValidity()) return; // keep HTML5 field validation

                submitting = true;
                grecaptcha.ready(function () {
                    grecaptcha.execute(siteKey, { action: action }).then(function (token) {
                        input.value = token;
                        form.submit();
                    }).catch(function () {
                        submitting = false;
                    });
                });
            });
        })();
    </script>
@endif
