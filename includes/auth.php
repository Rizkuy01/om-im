<?php
class Auth {
    private $connUser;
    private $connIMOM;
    private $connISD;

    public function __construct($connUser, $connIMOM, $connISD) {
        $this->connUser = $connUser;
        $this->connIMOM = $connIMOM;
        $this->connISD  = $connISD;
    }

    public function login($npk, $password, $captcha) {
        session_start();
        $result = ['error' => '', 'redirect' => ''];

        // === Validasi Captcha ===
        if (!isset($_SESSION['captcha']) || strcasecmp($_SESSION['captcha'], $captcha) !== 0) {
            $result['error'] = 'Captcha salah!';
            return $result;
        }

        // === Ambil user dari ct_users (db lembur1) ===
        $stmt = $this->connUser->prepare("SELECT * FROM ct_users WHERE npk = ? LIMIT 1");
        $stmt->bind_param("s", $npk);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($password, $user['pwd'])) {
            $result['error'] = 'NPK atau Password salah!';
            return $result;
        }

        // Normalisasi dept
        $deptClean = ucwords(strtolower(trim($user['dept'])));

        // Cek departemen di om_im.department
        $stmtDept = $this->connIMOM->prepare("SELECT id, dept_name FROM department WHERE LOWER(dept_name)=LOWER(?) LIMIT 1");
        $stmtDept->bind_param("s", $deptClean);
        $stmtDept->execute();
        $rowDept = $stmtDept->get_result()->fetch_assoc();
        $stmtDept->close();

        if (!$rowDept) {
            $result['error'] = "Dept {$user['dept']} tidak ditemukan di database om_im.";
            return $result;
        }

        $deptId   = $rowDept['id'];
        $deptName = $rowDept['dept_name'];

        // === Ambil no hp dari db isd.hp ===
        $stmtHp = $this->connISD->prepare("SELECT no_hp FROM hp WHERE npk = ? LIMIT 1");
        $stmtHp->bind_param("s", $npk);
        $stmtHp->execute();
        $rowHp = $stmtHp->get_result()->fetch_assoc();
        $stmtHp->close();

        if (!$rowHp || empty($rowHp['no_hp'])) {
            $result['error'] = "Nomor HP untuk NPK {$npk} tidak ditemukan di database ISD.";
            return $result;
        }

        $noHp = $rowHp['no_hp'];

        // === Generate OTP ===
        $otpCode   = rand(100000, 999999);
        $createdAt = date('Y-m-d H:i:s');
        $expiredAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Simpan OTP ke table om_im.otp
        $stmtOtp = $this->connIMOM->prepare("
            INSERT INTO otp (npk, no_hp, kode_otp, created_at, expired_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtOtp->bind_param("ssiss", $npk, $noHp, $otpCode, $createdAt, $expiredAt);
        $stmtOtp->execute();
        $stmtOtp->close();

        // === Simpan ke session sementara ===
        $_SESSION['pending_user'] = [
            'npk'      => $user['npk'],
            'username' => $user['full_name'],
            'dept'     => $deptName,
            'dept_id'  => $deptId,
            'otp'      => $otpCode, // ⚠️ untuk testing, hapus kalau sudah kirim via SMS
        ];
        unset($_SESSION['captcha']);

        // Redirect sesuai dept
        if ($deptName === 'QA' || $deptName === 'MIS' || stripos($deptName, 'Production') === 0) {
            if (stripos($deptName, 'Production') === 0) {
                $_SESSION['pending_user']['redirect_after_otp'] = "index.php?page=workstations&dept_id=" . $deptId;
            } else {
                $_SESSION['pending_user']['redirect_after_otp'] = "index.php?page=main_dashboard";
            }
            $result['redirect'] = "verify_otp.php";
        } else {
            $result['error'] = "Akses untuk departemen {$deptName} belum diatur.";
        }

        return $result;
    }
}
