<?php
// $pageTitle variable সেট করা হচ্ছে — যদি কোনো page আগে থেকে $pageTitle সেট না করে থাকে,
// তাহলে default হিসেবে 'Billing Dashboard' ব্যবহার করা হবে (?? হলো null coalescing operator)
$pageTitle = $pageTitle ?? 'Billing Dashboard'; ?>
<!DOCTYPE html>
<!-- HTML5 document type ঘোষণা করা হচ্ছে — browser-কে জানানো হচ্ছে এটি একটি আধুনিক HTML page -->
<html>
<head>
    <!-- Browser tab-এ page-এর নাম দেখানো হচ্ছে — htmlspecialchars() দিয়ে XSS attack থেকে রক্ষা করা হচ্ছে -->
    <!-- যেমন: "Billing Dashboard | Billing" এভাবে দেখাবে -->
    <title><?= htmlspecialchars($pageTitle) ?> | Billing</title>

    <!-- প্রজেক্টের নিজস্ব CSS file (style.css) যুক্ত করা হচ্ছে — page-এর design/layout এখান থেকে আসে -->
    <link rel="stylesheet" href="style.css">

    <!-- Font Awesome icon library CDN থেকে লোড করা হচ্ছে — sidebar ও button-এ icon দেখানোর জন্য -->
    <!-- যেমন: fa-home, fa-plus-circle ইত্যাদি icon এই library থেকে আসে -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
