<?php include 'db.php'; ?>
<?php include 'header.php'; ?>

<section class="bg-white">
    <div class="max-w-7xl mx-auto px-4 py-16">

        <!-- PAGE HEADER -->
        <div class="text-center mb-16">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-4">
                Contact Support
            </h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Have a question or need help? Our support team is here for you.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">

            <!-- CONTACT FORM -->
            <div class="bg-gray-50 p-8 rounded-xl border shadow-sm">
                <form action="#" method="POST" class="space-y-5">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Name
                        </label>
                        <input type="text" placeholder="John Doe"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input type="email" placeholder="john@example.com"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Subject
                        </label>
                        <select class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option>Report a Problem</option>
                            <option>Billing Question</option>
                            <option>General Inquiry</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Message
                        </label>
                        <textarea rows="5" placeholder="How can we help?"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition">
                        Send Message
                    </button>

                </form>
            </div>

            <!-- CONTACT INFO -->
            <div class="space-y-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        Get in Touch
                    </h2>
                    <p class="text-gray-600">
                        Reach us through email or phone. We usually respond within 24 hours.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-100 text-blue-600 p-3 rounded-lg">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span class="text-gray-700 font-medium">
                            support@automarket.com
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="bg-blue-100 text-blue-600 p-3 rounded-lg">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span class="text-gray-700 font-medium">
                            (+63) 927 952 1545
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
