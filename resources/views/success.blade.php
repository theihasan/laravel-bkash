<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - bKash</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Titillium Web', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-gray-50">
<main class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto">
        <!-- Payment Success Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header Section with bKash Branding -->
            <div class="bg-green-600 px-6 py-8 text-center">
                <div class="flex justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Payment Successful</h1>
                <p class="text-green-100">Your bKash payment has been processed successfully</p>
            </div>

            <!-- Payment Information Section -->
            <div class="p-6">
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Transaction Details
                    </h2>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transaction ID</span>
                            <span class="font-medium">{{ $payment['trxID'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment ID</span>
                            <span class="font-medium">{{ $payment['paymentID'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date & Time</span>
                            <span class="font-medium">{{ now()->format('F d, Y • h:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Method</span>
                            <span class="font-medium">bKash</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount</span>
                            <span class="font-medium">৳ {{ $payment['amount'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">bKash Number</span>
                            <span class="font-medium">{{ $payment['customerMsisdn'] ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div class="bg-green-50 border border-green-100 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-green-800 font-medium mb-1">Payment Completed</h3>
                            <p class="text-green-700 text-sm">Your payment has been successfully processed.</p>
                        </div>
                    </div>
                </div>

                <!-- Options Section -->
                <div class="space-y-4">
                    <a href="/" class="block w-full py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-bold text-center">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
