<?php
// ====================================================
// logout.php — User Logout
// কাজ: user-কে system থেকে logout করানো
// ====================================================

// PHP session শুরু করা হচ্ছে (session data access করার জন্য আগে start করতে হয়)
session_start();

// সমস্ত session data মুছে ফেলা হচ্ছে — user আর logged in থাকবে না
session_destroy();

// login page-এ redirect করা হচ্ছে
header("Location: index.php");

// redirect-এর পরে বাকি কোনো PHP কোড যাতে না চলে
exit;
?>
