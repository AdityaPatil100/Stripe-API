@extends('layouts.main')
@section('title', 'Payment Form')

@section('content')
<main>
    <div class="row">
        <aside class="col-sm-6 offset-3">
            <article class="card">
                <div class="card-body p-5">
                    <h4 class="mb-4">Payment Details</h4>

                    <form id="payment-form">
                        @csrf
                        <div class="form-group">
                            <label for="full-name">Full Name (on card)</label>
                            <input type="text" class="form-control" id="full-name" name="fullName" placeholder="John Doe" required>
                        </div>

                        <div class="form-group">
                            <label for="card-element">Credit or Debit Card</label>
                            <div id="card-element" class="form-control"></div>
                            <div id="card-errors" class="text-danger mt-2"></div>
                        </div>

                        <button class="btn btn-primary btn-block mt-3" type="submit">Pay $20</button>
                    </form>
                </div>
            </article>
        </aside>
    </div>
</main>
@endsection
@section('scripts')
@parent
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe("{{ config('stripe.api_keys.publishable_key') }}");
const elements = stripe.elements();
const cardElement = elements.create('card');
cardElement.mount('#card-element');

document.getElementById('payment-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const fullName = document.getElementById('full-name').value;

    // Create the token
    const { token, error } = await stripe.createToken(cardElement, { name: fullName });

    if (error) {
        document.getElementById('card-errors').textContent = error.message;
    } else {
        // Log token for debugging
        console.log('Generated token:', token);

        // Send the token to the backend
        fetch('/payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ token: token.id, fullName: fullName })
        })
        .then(response => response.json())
        .then(data => {
            // alert(data.message);
        })
        .catch(error => {
            // console.error(error);
        });
    }
});

</script>
@endsection
