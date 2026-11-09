<?php

namespace App\Support;

use Illuminate\Support\Str;

class LaundryKnowledge
{
    public static function topics(): array
    {
        $prices = collect(LaundryWorkflow::SERVICES)->map(fn ($service, $name) => $name.' — ₱'.number_format($service['price'], 0).'/kg')->implode("\n");
        $extras = collect(LaundryWorkflow::EXTRAS)->map(fn ($price, $name) => $name.' ₱'.number_format($price, 0))->implode(', ');
        $detergents = collect(LaundryProducts::DETERGENTS)->pluck('name')->implode(', ');
        $softeners = collect(LaundryProducts::SOFTENERS)->pluck('name')->implode(' or ');
        $sachet = number_format(LaundryProducts::DETERGENT_SACHET_CENTS / 100, 0);
        $litre = number_format(LaundryProducts::SOFTENER_LITRE_CENTS / 100, 0);
        $cup = LaundryProducts::CUP_MICROLITRES / 1000;
        $times = implode(', ', LaundryWorkflow::TIMES);

        return [
            'booking' => self::entry('How do I book a pickup?', ['book', 'booking', 'pickup', 'pick up', 'schedule', 'mag book', 'magbook'],
                "1. Sign in or create an account.\n2. Add your phone number and pickup address in Profile settings.\n3. Select Book a pickup, choose a service, fabric, weight, products and extras.\n4. Choose an available date and time, review the total, and confirm.\n\nYou will receive an order reference and a tracking page.", ['booking', 'profile']),
            'process' => self::entry('How does Laundryhub work?', ['process', 'how it works', 'how does laundryhub', 'how does it work', 'steps', 'paano', 'about laundryhub', 'what is waku'],
                "Book and schedule → pickup → cleaning → ready for delivery → completed.\n\nWash & Fold shows Washing and Drying separately; other services show Processing. Follow updates in My orders. Ready means the team will arrange delivery; Completed means your laundry has been handed back.", ['booking', 'orders']),
            'prices' => self::entry('What are your prices?', ['price', 'prices', 'pricing', 'rates', 'rate', 'cost', 'how much', 'magkano', 'presyo'],
                $prices."\n\nYour total is the service rate × weight, plus selected detergent, softener and extras. Review the full breakdown before confirming. For example, 3 kg of Wash & Fold is ₱".number_format(3 * LaundryWorkflow::SERVICES['Wash & Fold']['price'], 0).' before products and extras.', ['services', 'booking']),
            'products' => self::entry('Which detergents can I choose?', ['detergent', 'detergents', 'sachet', 'sachets', 'ariel', 'breeze', 'surf'],
                "Choose {$detergents} liquid detergent at ₱{$sachet} per sachet, or the Bring my own option without a shop detergent charge. Products are available for Wash & Fold and Express Service. Choose a brand and quantity in the booking form; the total updates as you go.", ['booking']),
            'softener' => self::entry('How is fabric softener measured?', ['softener', 'downy', 'conditioner', 'fabcon', 'cup', 'cups', 'ml', 'liter', 'litre'],
                "Choose {$softeners} fabric softener, or skip it. The rate is ₱{$litre} per liter. The booking measure is exactly {$cup} mL per illustrated cup, adjustable in half-cup steps.\n\nThe charge uses the actual volume and rounds to centavos. This is the shop's booking measure; at home, follow the product's dosing label.", ['booking']),
            'extras' => self::entry('Which extras are available?', ['extra', 'extras', 'add on', 'add ons', 'scent boost', 'hang dry', 'packaging', 'package'],
                'The optional extras are '.$extras.'. They are added once per selected extra, rather than per kilogram. Select only what you need and check the final total before confirming.', ['booking']),
            'tracking' => self::entry('How do I track my laundry?', ['track', 'tracking', 'my order', 'my orders', 'my laundry', 'order status', 'reference', 'nasaan'],
                "Sign in to the account used for booking, open My orders, and select your order to view its tracking timeline and payment status. You can also use its order reference to open the tracking page.\n\nI can explain the stages, but I cannot look up a private order in this chat.", ['orders']),
            'pickup_times' => self::entry('Which pickup times can I choose?', ['pickup time', 'pickup times', 'time slots', 'available time', 'anong oras', 'collection time'],
                'The booking form offers '.$times.'. Pick today or a future date; times that have already passed cannot be booked. These are pickup options, not a statement of shop opening hours.', ['booking']),
            'turnaround' => self::entry('When will my laundry be ready?', ['how long', 'turnaround', 'same day', 'duration', 'ready', 'express', 'delivery time'],
                'Express Service provides priority processing. A guaranteed completion time or same-day promise is not published on this site. The team arranges delivery once an order reaches Ready for delivery. Check your tracking page or ask the shop about a particular deadline.', ['orders', 'services']),
            'payment' => self::entry('How does payment work?', ['payment', 'payments', 'pay', 'paid', 'gcash', 'cash', 'bank', 'credit card'],
                'The shop can record payments received by Cash, GCash, or Bank. Payment status is separate from cleaning progress. This website does not process an online payment or provide a verified bank/GCash account in chat. Confirm payment details with the shop; My orders shows whether a payment has been recorded.', ['orders']),
            'changes' => self::entry('Can I change or cancel a pickup?', ['reschedule', 'cancel', 'cancellation', 'change my pickup', 'refund', 'move my pickup'],
                'Ask the shop to change a pickup before collection. Staff can reschedule eligible orders; this chat cannot change or cancel a booking, issue a refund, or promise refund eligibility. Refunds recorded by staff reflect money already returned outside the website.', ['orders']),
            'account' => self::entry('Do I need an account?', ['account', 'login', 'log in', 'sign in', 'register', 'password', 'phone number', 'pickup address'],
                'You can ask FAQs without an account. Booking and private tracking require signing in. Create an account if needed, then add your contact number and collection address in Profile settings. Customers, admins and employees use the same sign-in page.', ['login', 'profile']),
            'preparation' => self::entry('How should I prepare a pickup?', ['prepare', 'preparation', 'ready for pickup', 'pack my', 'pockets', 'special instructions'],
                'Empty pockets, separate delicate items, and have the load ready at the saved collection address. Add stains, care-label restrictions, and other requests to Special instructions before confirming your booking. Choose the matching fabric and wash style.', ['booking']),
            'delicates' => self::entry('What about delicate clothes?', ['delicate', 'delicates', 'silk', 'wool', 'dry cleaning', 'dry clean'],
                'Follow the garment label. Keep delicate items separate and tell the shop about restrictions. Choose Dry Cleaning for garments whose label requires it.', ['services', 'care']),
            'stains' => self::entry('What should I do about a stain?', ['stain', 'stains', 'coffee', 'grease', 'oil stain', 'blood'],
                'Tell the shop what caused the stain. For home care, check the label, blot gently, and test a suitable stain remover in a hidden spot. Recheck before drying: heat may set a remaining stain. No removal result is guaranteed.', ['stains']),
            'sorting' => self::entry('How do I sort clothes?', ['sort', 'sorting', 'whites', 'colors', 'colours', 'dark clothes', 'color bleed'],
                'Separate whites, colors, heavy soil, and delicate fabrics. Group items with compatible care labels. Wash new dark or bright garments separately at first.', ['care']),
            'care_labels' => self::entry('What do care labels mean?', ['care label', 'labels', 'symbols', 'washing symbol', 'care instructions'],
                'The tub concerns washing, triangle bleaching, square drying, iron ironing, and circle professional care. A crossed-out symbol means avoid that treatment. Follow temperature limits.', ['care']),
            'drying' => self::entry('How should I dry my clothes?', ['dry', 'drying', 'tumble', 'shrink', 'shrinkage', 'air dry', 'dryer', 'musty', 'damp'],
                'Use the drying method on the label, especially for heat-sensitive items. Let clothes dry fully before storage. Hang Dry is an optional shop extra.', ['care', 'booking']),
            'detergent_dose' => self::entry('How much detergent should I use at home?', ['how much detergent', 'dosage', 'dose', 'too much detergent', 'detergent amount'],
                'Use the product label and measuring guide for your load, soil level, and machine. Extra detergent does not necessarily improve cleaning. The shop sachet price is a charge, not home-dosing advice.', ['care']),
            'contact' => self::entry('Where is the shop and what are its hours?', ['shop address', 'shop location', 'opening hours', 'hours', 'contact', 'delivery fee', 'coverage', 'minimum load', 'discount', 'phone', 'location'],
                'Shop contact details, opening hours, delivery coverage, delivery fees and minimum-load policies are not published here. Please confirm these details directly with the shop before booking if they affect your order.', ['services']),
        ];
    }

    private static function entry(string $question, array $keywords, string $answer, array $links): array
    {
        return compact('question', 'keywords', 'answer', 'links');
    }

    public static function match(string $question): ?string
    {
        $text = ' '.preg_replace('/[^\p{L}\p{N}]+/u', ' ', Str::lower($question)).' ';
        $best = null;
        $bestScore = 0;
        foreach (self::topics() as $id => $topic) {
            $score = 0;
            foreach ($topic['keywords'] as $keyword) {
                if (str_contains($text, ' '.$keyword.' ')) {
                    $specific = in_array($id, ['products', 'softener', 'extras', 'preparation', 'stains', 'changes'], true);
                    $score += $keyword === 'how much' ? 2 : (str_contains($keyword, ' ') ? 5 : ($specific ? 3 : 2));
                }
            }
            if ($score > $bestScore) {
                $best = $id;
                $bestScore = $score;
            }
        }

        return $best;
    }

    public static function links(array $ids): array
    {
        $staff = auth('staff')->check();
        $catalog = [
            'booking' => ['label' => $staff ? 'New order' : 'Book a pickup', 'url' => route($staff ? 'staff.orders.create' : 'booking.create')],
            'orders' => ['label' => $staff ? 'Order queue' : 'My orders', 'url' => route($staff ? 'staff.orders.index' : 'customer.orders.history')],
            'services' => ['label' => 'Services & prices', 'url' => route('landing').'#services'],
            'profile' => ['label' => 'Profile settings', 'url' => route($staff ? 'staff.profile' : 'profile.settings')],
            'login' => ['label' => 'Sign in', 'url' => route('login.form')],
            'care' => ['label' => 'Laundry care guide · ACI', 'url' => 'https://www.cleaninginstitute.org/cleaning-tips/clothes/laundry-basics'],
            'stains' => ['label' => 'Stain guide · ACI', 'url' => 'https://www.cleaninginstitute.org/cleaning-tips/clothes/stain-removal-guide'],
        ];

        return array_values(array_intersect_key($catalog, array_flip($ids)));
    }

    public static function reply(?string $topic): array
    {
        $entry = self::topics()[$topic ?? ''] ?? null;

        return [
            'answer' => $entry['answer'] ?? 'I can help with Laundryhub booking, prices, pickup, tracking, products and garment care. Try a question below, or tell me which part you want explained.',
            'source' => 'faq',
            'topic' => $entry ? $topic : null,
            'links' => self::links($entry['links'] ?? ['booking', 'services']),
            'suggestions' => match ($topic) {
                'booking', 'account', 'pickup_times' => ['How should I prepare a pickup?', 'What are your prices?'],
                'prices', 'products', 'softener', 'extras' => ['How is fabric softener measured?', 'Which extras are available?'],
                'tracking', 'process', 'turnaround' => ['How does payment work?', 'Can I change or cancel a pickup?'],
                'stains', 'sorting', 'care_labels', 'drying', 'delicates', 'detergent_dose' => ['How do I sort clothes?', 'What do care labels mean?'],
                default => ['How do I book a pickup?', 'How do I track my laundry?', 'What should I do about a stain?'],
            },
        ];
    }
}
