<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Stripe Laravel Gateway</title>
        <script src="https://js.stripe.com/v3/"></script>
        <link rel="icon" href="favicon.svg" type="image/svg+xml">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-700">Stripe Payment</h1>
            <p class="text-gray-500 mt-2">Enter the amount to pay</p>
        </div>

        <form id="payment-form" class="space-y-4">
            <!-- Input Field -->
            <div class="relative">
                <!-- Dollar icon inside the input field -->
                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <input id="amount" type="number" min="1" placeholder="Enter amount" required
                    class="pl-8 pr-4 py-2 border border-gray-300 rounded-md text-sm shadow-sm w-full placeholder-gray-400 focus:ring-blue-500 focus:outline-none">
            </div>

            <!-- Stripe Card Element -->
            <div id="card-element" class="bg-gray-50 border border-gray-300 rounded-md p-4"></div>

            <!-- Submit Button -->
            <button id="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-md hover:bg-blue-700 transition disabled:bg-gray-400" disabled>
                Pay Now
            </button>

            <!-- Spinner -->
            <div id="spinner" class="text-center hidden">
                <svg class="animate-spin h-6 w-6 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </div>

            <!-- Payment Result -->
            <div id="payment-result" class="text-center text-sm hidden"></div>
        </form>

        <p class="text-center text-sm text-gray-400 mt-6">Your payment is secure and encrypted.</p>
    </div>

    <script>
        const stripe = Stripe("{{ config('stripe.test.pk') }}");
        const elements = stripe.elements();
        const card = elements.create('card', { style: { base: { fontSize: '16px', color: '#32325d' } } });
        card.mount('#card-element');

        const amountInput = document.getElementById('amount');
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit');
        const spinner = document.getElementById('spinner');
        const resultMessage = document.getElementById('payment-result');

        // Enable button when a valid amount is entered
        amountInput.addEventListener('input', () => {
            submitButton.disabled = !amountInput.value || amountInput.value <= 0;
        });

        // Handle form submission
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            toggleLoading(true);

            try {
                // Step 1: Create PaymentIntent
                const response = await fetch("{{ route('createPaymentIntent') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                    body: JSON.stringify({ amount: amountInput.value }),
                });

                const { clientSecret, error } = await response.json();

                if (error) throw new Error(error);

                // Step 2: Confirm PaymentIntent
                const { paymentIntent, error: stripeError } = await stripe.confirmCardPayment(clientSecret, {
                    payment_method: { card, billing_details: { name: 'Customer Name' } },
                });

                if (stripeError) throw new Error(stripeError.message);

                handlePaymentResult(paymentIntent.status === 'succeeded');
            } catch (error) {
                handlePaymentResult(false, error.message);
            }
        });

        // Toggle loading spinner and disable button
        function toggleLoading(isLoading) {
            spinner.classList.toggle('hidden', !isLoading);
            submitButton.disabled = isLoading;
        }

        // Handle payment result
        function handlePaymentResult(success, message = '') {
            toggleLoading(false);
            resultMessage.textContent = success ? 'Payment successful!' : `Payment failed: ${message}`;
            resultMessage.className = `text-center text-sm ${success ? 'text-green-500' : 'text-red-500'}`;
            resultMessage.classList.remove('hidden');
        }
    </script>
</body>
</html>
