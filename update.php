<?php
header('Content-Type: text/html; charset=utf-8');

function env_or_fail($key) {
  $val = getenv($key);
  if ($val === false || $val === '') {
    http_response_code(500);
    die("환경 변수 누락: {$key}");
  }
  return $val;
}
$DB_HOST = env_or_fail('DB_HOST');   // 예: mydb.cluster-abc123.ap-northeast-2.rds.amazonaws.com
$DB_USER = env_or_fail('DB_USER');   // 예: appuser
$DB_PASS = env_or_fail('DB_PASS');   // 예: *** (개발은 임시, 운영은 Secrets 권장)
$DB_NAME = env_or_fail('DB_NAME');   // 예: sqlDB

// ✅ DB 연결
$con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$con) { die("MySQL 접속 실패: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8')); }
mysqli_set_charset($con, "utf8mb4");

/* ✅ GET 파라미터 */
$reqUserId = $_GET['userID'] ?? '';
if ($reqUserId === '') {
  echo "<!doctype html><meta charset='utf-8'><p>요청한 회원 아이디가 없습니다.</p><p><a href='main.html'>← 초기 화면</a></p>";
  exit;
}

/* ✅ Prepared Statement로 안전 조회 */
$sql = "SELECT userID, name, birthYear, addr, mobile1, mobile2, height, mDATE FROM userTBL WHERE userID = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $reqUserId);
mysqli_stmt_execute($stmt);
$ret = mysqli_stmt_get_result($stmt);

if (!$ret) {
  echo "<!doctype html><meta charset='utf-8'><p>데이터 조회 실패!</p><p>원인: " . htmlspecialchars(mysqli_error($con), ENT_QUOTES, 'UTF-8') . "</p><p><a href='main.html'>← 초기 화면</a></p>";
  exit;
}
if (mysqli_num_rows($ret) === 0) {
  echo "<!doctype html><meta charset='utf-8'><p>" . htmlspecialchars($reqUserId, ENT_QUOTES, 'UTF-8') . " 아이디의 회원이 없습니다.</p><p><a href='main.html'>← 초기 화면</a></p>";
  exit;
}

/* ✅ 데이터 준비 + XSS 방지용 헬퍼 */
$row = mysqli_fetch_assoc($ret);
$h = fn($v) => htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');

$userID    = $row['userID']    ?? '';
$name      = $row['name']      ?? '';
$birthYear = $row['birthYear'] ?? '';
$addr      = $row['addr']      ?? '';
$mobile1   = $row['mobile1']   ?? '';
$mobile2   = $row['mobile2']   ?? '';
$height    = $row['height']    ?? '';
$mDATE     = $row['mDATE']     ?? '';

mysqli_free_result($ret);
mysqli_stmt_close($stmt);
mysqli_close($con);
?>
<!doctype html>
<html lang="ko" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>회원 정보 수정 - <?= $h($userID) ?></title>
  <style>
    :root{
      --bg:#0e1229; --card:#151b3a; --text:#eaf0ff; --muted:#9aa3c7;
      --accent:#7ca6ff; --accent2:#8df3ff; --ok:#29d398; --warn:#ffd166; --danger:#ff6b6b;
      --border:rgba(255,255,255,.12); --input:#0d1231; --shadow:0 16px 44px rgba(0,0,0,.38);
    }
    [data-theme="light"]{
      --bg:#f3f6ff; --card:#ffffff; --text:#1a2038; --muted:#586285;
      --accent:#4c7dff; --accent2:#3bd6ff; --ok:#10b981; --warn:#f59e0b; --danger:#ef4444;
      --border:rgba(10,20,60,.14); --input:#f7faff; --shadow:0 12px 28px rgba(32,40,94,.12);
    }
    *{box-sizing:border-box}
    body{
      margin:0; background:
        radial-gradient(1000px 520px at 12% -10%, rgba(124,166,255,.18), transparent 60%),
        radial-gradient(900px 600px at 110% 0%, rgba(141,243,255,.18), transparent 60%),
        var(--bg);
      color:var(--text);
      font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Noto Sans KR',Pretendard,sans-serif;
      min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
    }
    .app{width:min(980px,100%)}
    .nav{display:flex; justify-content:space-between; align-items:center; margin-bottom:14px}
    .title{display:flex; gap:10px; align-items:center}
    .title h1{margin:0; font-size:22px}
    .badge{display:inline-flex; gap:8px; align-items:center; padding:8px 12px; border-radius:999px;
      background:linear-gradient(90deg,var(--accent),var(--accent2)); color:#0c1233; font-weight:900;}
    .btn{
      appearance:none; border:1px solid var(--border); background:transparent; color:var(--text);
      padding:10px 14px; border-radius:12px; cursor:pointer; font-weight:700;
    }
    .btn:hover{filter:brightness(1.08)}
    .card{background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.01)), var(--card);
      border:1px solid var(--border); border-radius:22px; box-shadow:var(--shadow); overflow:hidden;}
    .head{display:flex; justify-content:space-between; align-items:center; padding:18px 18px 0}
    .wrap{padding:18px}
    .grid{display:grid; grid-template-columns:repeat(2,1fr); gap:14px}
    @media (max-width:820px){ .grid{grid-template-columns:1fr} }
    .field{
      background:var(--input); border:1px solid var(--border); border-radius:16px; padding:12px; position:relative;
    }
    .label{display:block; font-size:12px; color:var(--muted); margin-bottom:6px}
    .input{width:100%; background:transparent; border:none; outline:none; color:var(--text); font-size:16px; padding:6px 0}
    .side{position:absolute; right:10px; top:10px; opacity:.8}
    .hint{font-size:12px; color:var(--muted); margin-top:6px}
    .hint.ok{color:var(--ok)} .hint.err{color:var(--danger)} .hint.warn{color:var(--warn)}
    .row{display:flex; gap:10px; align-items:center}
    .footer{display:flex; justify-content:space-between; align-items:center; padding:0 18px 18px; color:var(--muted)}
    .link{color:var(--text); text-decoration:none; border:1px solid var(--border); padding:10px 12px; border-radius:10px}
    .submit{background:linear-gradient(90deg,var(--accent),var(--accent2)); color:#0c1233;
      border:none; padding:12px 18px; border-radius:12px; font-weight:900; cursor:pointer}
    .submit[disabled]{opacity:.65; cursor:not-allowed}
    .danger{background:linear-gradient(90deg,var(--danger),#ff9f9f); color:#2b0b0b}
    .progress{height:10px; background:rgba(255,255,255,.06); border:1px solid var(--border); border-radius:999px; overflow:hidden}
    .bar{height:100%; width:0%; background:linear-gradient(90deg,var(--ok),var(--accent)); transition:width .25s ease}
    .toast{position:fixed; right:16px; bottom:16px; display:flex; flex-direction:column; gap:10px; z-index:50}
    .toast .msg{background:var(--card); border:1px solid var(--border); color:var(--text); padding:12px 14px; border-radius:12px; box-shadow:var(--shadow); animation:slide .2s ease}
    @keyframes slide{from{opacity:0; transform:translateY(8px)} to{opacity:1; transform:translateY(0)}}
    .sr{position:absolute; left:-9999px}
  </style>
</head>
<body>
  <div class="app">
    <nav class="nav">
      <div class="title">
        <h1>🛠️ 회원 정보 수정</h1>
        <span class="badge">ID: <?= $h($userID) ?></span>
      </div>
      <div class="actions">
        <button class="btn" id="toggle">🌗 테마 전환</button>
      </div>
    </nav>

    <section class="card" role="region" aria-label="회원 정보 수정 폼">
      <div class="head">
        <h2>✏️ 프로필 편집</h2>
      </div>
      <form class="wrap" method="post" action="update_result.php" id="editForm" novalidate>
        <!-- 진행도 -->
        <div class="progress" aria-hidden="true" style="margin-bottom:14px"><div class="bar" id="bar"></div></div>
        <span id="live" class="sr" aria-live="polite"></span>

        <div class="grid">
          <!-- 아이디 (읽기전용) -->
          <div class="field">
            <label class="label" for="userID">👤 아이디</label>
            <input class="input" id="userID" name="userID" type="text" value="<?= $h($userID) ?>" readonly>
            <span class="side">🔒</span>
            <div class="hint">아이디는 수정할 수 없습니다.</div>
          </div>

          <!-- 이름 -->
          <div class="field" data-field="name">
            <label class="label" for="name">🧑 이름</label>
            <input class="input" id="name" name="name" type="text" value="<?= $h($name) ?>" placeholder="예: 홍길동" required maxlength="40">
            <span class="side" data-icon>✍️</span>
            <div class="hint" id="hint-name">실명을 입력해주세요</div>
          </div>

          <!-- 출생년도 -->
          <div class="field" data-field="birthYear">
            <label class="label" for="birthYear">🎂 출생년도</label>
            <input class="input" id="birthYear" name="birthYear" type="number" value="<?= $h($birthYear) ?>" placeholder="예: 1990"
                   inputmode="numeric" min="1920" max="2025" required>
            <span class="side" data-icon>📅</span>
            <div class="hint" id="hint-birthYear">YYYY 형식 (1920~2025)</div>
          </div>

          <!-- 지역 -->
          <div class="field" data-field="addr">
            <label class="label" for="addr">🗺️ 지역</label>
            <input class="input" id="addr" name="addr" list="regions" value="<?= $h($addr) ?>" placeholder="예: 서울특별시" required>
            <datalist id="regions">
              <option value="서울특별시"><option value="부산광역시"><option value="대구광역시">
              <option value="인천광역시"><option value="광주광역시"><option value="대전광역시">
              <option value="울산광역시"><option value="세종특별자치시"><option value="경기도">
              <option value="강원특별자치도"><option value="충청북도"><option value="충청남도">
              <option value="전북특별자치도"><option value="전라남도"><option value="경상북도">
              <option value="경상남도"><option value="제주특별자치도">
            </datalist>
            <span class="side" data-icon>📍</span>
            <div class="hint" id="hint-addr">도시/도 선택 또는 직접 입력</div>
          </div>

          <!-- 휴대폰 국번 -->
          <div class="field" data-field="mobile1">
            <label class="label" for="mobile1">☎️ 휴대폰 국번</label>
            <input class="input" id="mobile1" name="mobile1" list="mobilePrefix" value="<?= $h($mobile1) ?>" placeholder="예: 010"
                   inputmode="numeric" required pattern="^01[016789]$" maxlength="3">
            <datalist id="mobilePrefix">
              <option value="010"><option value="011"><option value="016"><option value="017"><option value="018"><option value="019">
            </datalist>
            <span class="side" data-icon>📞</span>
            <div class="hint" id="hint-mobile1">예: 010</div>
          </div>

          <!-- 휴대폰 번호 -->
          <div class="field" data-field="mobile2">
            <label class="label" for="mobile2">📞 휴대폰 전화번호</label>
            <input class="input" id="mobile2" name="mobile2" type="text" value="<?= $h($mobile2) ?>" placeholder="예: 12345678"
                   inputmode="numeric" required pattern="^[0-9]{7,8}$" maxlength="8">
            <span class="side" data-icon>🔢</span>
            <div class="hint" id="hint-mobile2">하이픈 없이 7~8자리</div>
          </div>

          <!-- 신장 -->
          <div class="field" data-field="height">
            <label class="label" for="height">📏 신장(cm)</label>
            <input class="input" id="height" name="height" type="number" value="<?= $h($height) ?>" placeholder="예: 175"
                   inputmode="numeric" min="50" max="250" step="1" required>
            <span class="side" data-icon>📐</span>
            <div class="hint" id="hint-height">50 ~ 250 사이</div>
          </div>

          <!-- 가입일(읽기전용) -->
          <div class="field">
            <label class="label" for="mDATE">🗓️ 가입일</label>
            <input class="input" id="mDATE" name="mDATE" type="date" value="<?= $h($mDATE) ?>" readonly>
            <span class="side">🔒</span>
            <div class="hint">가입일은 수정할 수 없습니다.</div>
          </div>
        </div>

        <div class="footer">
          <div style="display:flex; gap:8px; align-items:center;">
            <a class="link" href="main.html">🏠 초기 화면</a>
            <a class="link" href="select.php">📋 회원 목록</a>
          </div>
          <div style="display:flex; gap:8px;">
            <button type="reset" class="btn" id="resetBtn">🧹 초기화</button>
            <button type="submit" class="submit" id="submitBtn">💾 정보 수정</button>
          </div>
        </div>
      </form>
    </section>
  </div>

  <!-- 토스트 -->
  <div class="toast" id="toast"></div>

  <script>
    // 🌗 테마 토글
    const root = document.documentElement;
    document.getElementById('toggle').addEventListener('click', ()=>{
      root.dataset.theme = (root.dataset.theme === 'light') ? 'dark' : 'light';
      toast(root.dataset.theme === 'light' ? '라이트 테마로 변경되었습니다.' : '다크 테마로 변경되었습니다.');
    });

    // ✅ 폼 유효성 검사/진행도/미저장 경고
    const form = document.getElementById('editForm');
    const fields = ['name','birthYear','addr','mobile1','mobile2','height'].map(id=>document.getElementById(id));
    const bar = document.getElementById('bar');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn = document.getElementById('resetBtn');
    const live = document.getElementById('live');
    const toastBox = document.getElementById('toast');

    // 숫자만 허용 보조
    function onlyDigits(el){ el.value = el.value.replace(/[^\d]/g,''); }
    ['birthYear','mobile1','mobile2','height'].forEach(id=>{
      const el = document.getElementById(id);
      el.addEventListener('input', ()=>onlyDigits(el));
    });

    // 힌트/아이콘 업데이트
    function setHint(el, type, msg){
      const box = el.closest('.field');
      const hint = box.querySelector('.hint');
      const icon = box.querySelector('[data-icon]');
      hint.classList.remove('ok','warn','err');
      if(type==='ok'){ hint.classList.add('ok'); icon && (icon.textContent = '✅'); }
      if(type==='warn'){ hint.classList.add('warn'); icon && (icon.textContent = '⚠️'); }
      if(type==='err'){ hint.classList.add('err'); icon && (icon.textContent = '⛔'); }
      if(msg) hint.textContent = msg;
    }

    // 필드별 검증
    function validate(el){
      if(!el.value.trim()){ setHint(el,'warn','값을 입력해주세요'); return false; }
      if(!el.checkValidity()){
        switch(el.name){
          case 'birthYear': setHint(el,'err','출생년도: 1920~2025 사이 숫자'); break;
          case 'mobile1': setHint(el,'err','국번: 010/011/016/017/018/019'); break;
          case 'mobile2': setHint(el,'err','번호: 하이픈 없이 7~8자리'); break;
          case 'height': setHint(el,'err','신장: 50~250 사이 숫자'); break;
          default: setHint(el,'err','입력값을 확인해주세요');
        }
        return false;
      }
      setHint(el,'ok','좋습니다!');
      return true;
    }

    function refreshProgress(){
      const okCount = fields.reduce((n,el)=> n + (validate(el)?1:0), 0);
      const pct = Math.round(okCount / fields.length * 100);
      bar.style.width = pct + '%';
      live.textContent = `완료도 ${pct}%`;
    }
    fields.forEach(el=>{
      el.addEventListener('input', refreshProgress);
      el.addEventListener('blur',  ()=>validate(el));
    });
    refreshProgress();

    // ⛔ 미저장 변경 이탈 방지
    const initial = new Map(fields.map(el=>[el.name, el.value]));
    let dirty = false;
    fields.forEach(el=> el.addEventListener('input', ()=>{
      dirty = fields.some(x => x.value !== initial.get(x.name));
    }));
    window.addEventListener('beforeunload', (e)=>{
      if(dirty){ e.preventDefault(); e.returnValue = ''; }
    });
    resetBtn.addEventListener('click', ()=>{
      setTimeout(()=>{ dirty = false; refreshProgress(); toast('입력이 초기화되었습니다.'); }, 0);
    });

    // ⛔ 중복 제출 방지 + 최종 검증
    form.addEventListener('submit', (e)=>{
      let allOk = true;
      fields.forEach(el => { if(!validate(el)) allOk = false; });
      if(!allOk){
        e.preventDefault();
        toast('필수 항목을 다시 확인해주세요.');
        return;
      }
      submitBtn.disabled = true;
      submitBtn.textContent = '⏳ 전송 중...';
      window.removeEventListener('beforeunload', ()=>{});
    });

    // 🔔 토스트
    function toast(text, ttl=2400){
      const el = document.createElement('div');
      el.className = 'msg'; el.textContent = text;
      toastBox.appendChild(el);
      setTimeout(()=>{ el.style.opacity='0'; el.style.transform='translateY(6px)'; }, ttl-300);
      setTimeout(()=>{ toastBox.removeChild(el); }, ttl);
      live.textContent = text;
    }
  </script>
</body>
</html>
