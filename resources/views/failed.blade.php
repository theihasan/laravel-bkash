<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - bKash</title>
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
        <!-- Payment Failed Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header Section with bKash Branding -->
            <div class="bg-pink-600 px-6 py-8 text-center">
                <div class="flex justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Payment Failed</h1>
                <p class="text-pink-100">We were unable to process your bKash payment</p>
            </div>

            <!-- Payment Information Section -->
            <div class="p-6">
                <!-- Error Message -->
                <div class="bg-red-50 border border-red-100 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-red-800 font-medium mb-1">Payment Error</h3>
                            <p class="text-red-700 text-sm">{{ $error ?? 'The transaction was declined by bKash. This could be due to insufficient balance or a temporary issue with the payment gateway.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Options Section -->
                <div class="space-y-4">
                    <a href="javascript:history.back()" class="block w-full py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors font-bold text-center">
                        Try Again
                    </a>

                    <div class="text-center">
                        <a href="/" class="inline-flex items-center text-[#FF2D20] hover:underline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
