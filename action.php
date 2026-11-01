<?php
// কনফিগারেশন ফাইল লোড করা হচ্ছে - ডেটাবেস সেটিং ও সাধারণ কনফিগার এখানে থাকে
require_once 'includes/config.php';
// ফাংশন ফাইল লোড করা হচ্ছে - isLoggedIn() সহ অন্যান্য সাহায্যকারী ফাংশন এখানে আছে
require_once 'includes/functions.php';

// ব্যবহারকারী লগইন করা আছে কিনা যাচাই করা হচ্ছে
if (!$functions->isLoggedIn()) {
    // লগইন না থাকলে লগইন পেজে পাঠিয়ে দেওয়া হচ্ছে
    header("Location: index.php");
    // স্ক্রিপ্টের বাকি অংশ চালানো বন্ধ করা হচ্ছে
    exit();
}

// URL থেকে 'action' প্যারামিটার নেওয়া হচ্ছে, না পেলে খালি স্ট্রিং ডিফল্ট
$action = $_GET['action'] ?? '';
// URL থেকে 'id' প্যারামিটার নেওয়া হচ্ছে, না পেলে 0 ডিফল্ট (রেকর্ডের ID)
$id = $_GET['id'] ?? 0;

// $action এর মান অনুযায়ী বিভিন্ন কাজ করা হবে
switch($action) {
    // যখন action = 'mark_complete' - একটি বিল সম্পন্ন হিসেবে চিহ্নিত করা
    case 'mark_complete':
        // invoices টেবিলে নির্দিষ্ট ID এর বিলের complete_status = 1 করা হচ্ছে
        $conn->query("UPDATE invoices SET complete_status = 1 WHERE id = $id");
        // কাজ শেষে pending বিল তালিকায় ফিরে যাওয়া হচ্ছে
        header("Location: dashboard.php?page=pending");
        // switch থেকে বের হয়ে যাওয়া
        break;

    // যখন action = 'mark_incomplete' - একটি সম্পন্ন বিলকে আবার অসম্পন্ন করা
    case 'mark_incomplete':
        // invoices টেবিলে নির্দিষ্ট ID এর বিলের complete_status = 0 করা হচ্ছে
        $conn->query("UPDATE invoices SET complete_status = 0 WHERE id = $id");
        // কাজ শেষে complete বিল তালিকায় ফিরে যাওয়া হচ্ছে
        header("Location: dashboard.php?page=complete");
        // switch থেকে বের হয়ে যাওয়া
        break;

    // যখন action = 'hold_bill' - একটি বিল হোল্ডে রাখা (স্থগিত করা)
    case 'hold_bill':
        // URL থেকে হোল্ডের কারণ নেওয়া হচ্ছে, না পেলে খালি স্ট্রিং
        $reason = $_GET['reason'] ?? '';
        // SQL ইনজেকশন প্রতিরোধে prepared statement ব্যবহার করা হচ্ছে
        $stmt = $conn->prepare("UPDATE invoices SET hold_status = 1, hold_description = ? WHERE id = ?");
        // প্যারামিটার বাইন্ড করা হচ্ছে: "si" মানে string ($reason) এবং integer ($id)
        $stmt->bind_param("si", $reason, $id);
        // কোয়েরি কার্যকর করা হচ্ছে
        $stmt->execute();
        // কাজ শেষে pending বিল তালিকায় ফিরে যাওয়া হচ্ছে
        header("Location: dashboard.php?page=pending");
        // switch থেকে বের হয়ে যাওয়া
        break;

    // যখন action = 'release_hold' - হোল্ড থাকা বিলকে মুক্ত করা
    case 'release_hold':
        // hold_status = 0 এবং hold_description = NULL করে হোল্ড তুলে নেওয়া হচ্ছে
        $conn->query("UPDATE invoices SET hold_status = 0, hold_description = NULL WHERE id = $id");
        // কাজ শেষে hold বিল তালিকায় ফিরে যাওয়া হচ্ছে
        header("Location: dashboard.php?page=hold");
        // switch থেকে বের হয়ে যাওয়া
        break;

    // যখন action = 'delete_bill' - একটি বিল soft delete করা
    case 'delete_bill':
        // invoices টেবিল থেকে বিলটি hard delete না করে deleted_at সেট করা হচ্ছে
        $conn->query("UPDATE invoices SET deleted_at = NOW() WHERE id = $id");
        header("Location: dashboard.php?page=pending");
        break;

    // যখন action = 'edit_company' - একটি কোম্পানির নাম পরিবর্তন করা
    case 'edit_company':
        // URL থেকে নতুন কোম্পানির নাম নেওয়া হচ্ছে, না পেলে খালি স্ট্রিং
        $name = $_GET['name'] ?? '';
        // SQL ইনজেকশন প্রতিরোধে prepared statement ব্যবহার করা হচ্ছে
        $stmt = $conn->prepare("UPDATE companies SET company_name = ? WHERE id = ?");
        // প্যারামিটার বাইন্ড করা হচ্ছে: "si" মানে string ($name) এবং integer ($id)
        $stmt->bind_param("si", $name, $id);
        // কোয়েরি কার্যকর করা হচ্ছে
        $stmt->execute();
        // কাজ শেষে companies তালিকায় ফিরে যাওয়া হচ্ছে
        header("Location: dashboard.php?page=companies");
        // switch থেকে বের হয়ে যাওয়া
        break;

    // যখন action = 'delete_company' - একটি কোম্পানি soft delete করা
    case 'delete_company':
        // companies টেবিল থেকে কোম্পানিটি hard delete না করে deleted_at সেট করা হচ্ছে
        $conn->query("UPDATE companies SET deleted_at = NOW() WHERE id = $id");
        header("Location: dashboard.php?page=companies");
        break;

    // উপরের কোনো case না মিললে এই default ব্লক চলবে
    default:
        // অজানা action হলে মূল ড্যাশবোর্ডে ফিরে যাওয়া হচ্ছে
        header("Location: dashboard.php");
        // switch থেকে বের হয়ে যাওয়া
        break;
}
?>
