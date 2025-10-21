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
if (!$con) {
  die("MySQL 접속 실패: " . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}

// ✅ 권장: 연결 단에서 문자셋 지정(세션 변수 set 대신)
mysqli_set_charset($con, "utf8mb4");

// ✅ 조회
$sql = "SELECT * FROM userTBL";
$ret = mysqli_query($con, $sql);
if (!$ret) {
  echo "userTBL 데이터 조회 실패!!!<br>";
  echo "실패 원인 : " . htmlspecialchars(mysqli_error($con), ENT_QUOTES, 'UTF-8');
  exit();
}
$count = mysqli_num_rows($ret);
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>회원 조회 결과</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root{
  --bg:#0f1226; --card:#15193a; --accent:#6a8dff; --accent2:#8ae6ff;
  --text:#e9ecff; --muted:#9aa3c7; --danger:#ff6b6b;
}
*{box-sizing:border-box}
body{
  margin:0; background:linear-gradient(135deg,#0f1226 0%,#09111a 100%);
  color:var(--text);
  font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Noto Sans KR',Pretendard,'Apple SD Gothic Neo',sans-serif;
}
.container{max-width:1200px; margin:40px auto; padding:24px}
.card{
  background: radial-gradient(1200px 600px at 20% 0%, rgba(106,141,255,.15), transparent 60%),
              linear-gradient(180deg,rgba(255,255,255,.02),rgba(255,255,255,.00));
  border:1px solid rgba(255,255,255,.06);
  border-radius:18px; box-shadow:0 10px 30px rgba(0,0,0,.35); overflow:hidden;
}
.header{display:flex; flex-wrap:wrap; align-items:center; gap:12px; padding:20px 20px 0}
.header h1{margin:0; font-size:22px; letter-spacing:.3px}
.badge{
  display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px;
  background:linear-gradient(90deg,var(--accent),var(--accent2));
  color:#0b1030; font-weight:700;
}
.tools{margin-left:auto; display:flex; align-items:center; gap:10px}
.search{position:relative}
.search input{
  background:#0e1331; color:var(--text); border:1px solid rgba(255,255,255,.1);
  padding:10px 12px 10px 36px; border-radius:12px; outline:none; width:240px;
}
.search .icon{position:absolute; left:10px; top:50%; transform:translateY(-50%); opacity:.7}
.kbd{
  font:600 12px/1 'JetBrains Mono','SFMono-Regular',monospace; padding:4px 6px;
  border:1px solid rgba(255,255,255,.2); border-radius:6px; background:rgba(0,0,0,.25)
}
.table-wrap{padding:20px; overflow:auto}
table{width:100%; border-collapse:separate; border-spacing:0 10px}
thead th{
  position:sticky; top:0; background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.02));
  color:var(--text); text-align:left; font-weight:700; padding:14px;
  border-bottom:1px solid rgba(255,255,255,.08); backdrop-filter:blur(6px); cursor:pointer;
}
tbody tr{
  background:linear-gradient(180deg,#0e1331 0%,#0a0f27 100%); border:1px solid rgba(255,255,255,.06);
  transition:transform .18s ease, box-shadow .18s ease;
}
tbody tr:hover{transform:translateY(-2px); box-shadow:0 10px 18px rgba(0,0,0,.35)}
tbody td{padding:14px; color:var(--text)}
tbody tr td:first-child, thead th:first-child{border-top-left-radius:12px; border-bottom-left-radius:12px}
tbody tr td:last-child, thead th:last-child{border-top-right-radius:12px; border-bottom-right-radius:12px}
.muted{color:var(--muted)}
.action{display:inline-flex; align-items:center; gap:6px; padding:8px 12px; border-radius:10px; font-weight:700; text-decoration:none}
.action.edit{background:rgba(138,230,255,.15); border:1px solid rgba(138,230,255,.35)}
.action.delete{background:rgba(255,107,107,.12); border:1px solid rgba(255,107,107,.35)}
.action:hover{filter:brightness(1.08)}
footer{padding:0 20px 20px; display:flex; justify-content:space-between; align-items:center; color:var(--muted)}
.sort-ind{font-size:12px; opacity:.7; margin-left:6px}
@media (max-width:860px){ .tools{width:100%} .search input{width:100%} }
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <div class="header">
      <h1>✨ 회원 조회 결과</h1>
      <span class="badge">📦 총 <strong><?= number_format($count) ?></strong>건</span>
      <div class="tools">
        <div class="search">
          <span class="icon">🔎</span>
          <input id="filter" type="text" placeholder="검색: 아이디, 이름, 지역, 전화…">
        </div>
        <span class="kbd">Tip: 헤더 클릭 → 정렬</span>
      </div>
    </div>

    <div class="table-wrap">
      <table id="members">
        <thead>
          <tr>
            <th data-col="userID">👤 아이디 <span class="sort-ind"></span></th>
            <th data-col="name">🧑 이름 <span class="sort-ind"></span></th>
            <th data-col="birthYear">🎂 출생년도 <span class="sort-ind"></span></th>
            <th data-col="addr">🗺️ 지역 <span class="sort-ind"></span></th>
            <th data-col="mobile1">☎️ 국번 <span class="sort-ind"></span></th>
            <th data-col="mobile2">📞 번호 <span class="sort-ind"></span></th>
            <th data-col="height">📏 키(cm) <span class="sort-ind"></span></th>
            <th data-col="mDATE">🗓️ 가입일 <span class="sort-ind"></span></th>
            <th>✏️ 수정</th>
            <th>🗑️ 삭제</th>
          </tr>
        </thead>
        <tbody>
<?php
if ($count > 0) {
  while ($row = mysqli_fetch_assoc($ret)) {
    // ✅ XSS 방지
    $userID    = htmlspecialchars($row['userID']    ?? '', ENT_QUOTES, 'UTF-8');
    $name      = htmlspecialchars($row['name']      ?? '', ENT_QUOTES, 'UTF-8');
    $birthYear = htmlspecialchars($row['birthYear'] ?? '', ENT_QUOTES, 'UTF-8');
    $addr      = htmlspecialchars($row['addr']      ?? '', ENT_QUOTES, 'UTF-8');
    $mobile1   = htmlspecialchars($row['mobile1']   ?? '', ENT_QUOTES, 'UTF-8');
    $mobile2   = htmlspecialchars($row['mobile2']   ?? '', ENT_QUOTES, 'UTF-8');
    $height    = htmlspecialchars($row['height']    ?? '', ENT_QUOTES, 'UTF-8');
    $mDATE     = htmlspecialchars($row['mDATE']     ?? '', ENT_QUOTES, 'UTF-8');

    echo "<tr data-userid='{$userID}'>";
    echo "<td data-label='아이디'>{$userID}</td>";
    echo "<td data-label='이름'>{$name}</td>";
    echo "<td data-label='출생년도'>{$birthYear}</td>";
    echo "<td data-label='지역'>{$addr}</td>";
    echo "<td data-label='국번'>{$mobile1}</td>";
    echo "<td data-label='번호'>{$mobile2}</td>";
    echo "<td data-label='키'>{$height}</td>";
    echo "<td data-label='가입일'>{$mDATE}</td>";
    echo "<td><a class='action edit' href='update.php?userID={$userID}'>🛠️ 편집</a></td>";
    echo "<td><a class='action delete' href='delete.php?userID={$userID}' onclick=\"return confirm('정말 삭제하시겠어요?\\n아이디: {$userID}')\">🧨 삭제</a></td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td class='muted' colspan='10'>데이터가 없습니다.</td></tr>";
}
mysqli_free_result($ret);
mysqli_close($con);
?>
        </tbody>
      </table>
    </div>

    <footer>
      <span>🌈 보기: <span class="muted">검색 · 정렬 · 고정 헤더 · 반응형</span></span>
      <a href="main.html" class="action" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15)">🏠 초기 화면</a>
    </footer>
  </div>
</div>

<script>
// 🔎 실시간 필터
const filter = document.getElementById('filter');
const tbody  = document.querySelector('#members tbody');
filter.addEventListener('input', () => {
  const kw = filter.value.trim().toLowerCase();
  Array.from(tbody.rows).forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(kw) ? '' : 'none';
  });
});

// ⬆️⬇️ 헤더 클릭 정렬
let sortState = { col:null, asc:true };
document.querySelectorAll('thead th[data-col]').forEach(th => {
  th.addEventListener('click', () => {
    const col = th.dataset.col;
    const asc = sortState.col === col ? !sortState.asc : true;
    sortState = { col, asc };

    // 인디케이터 업데이트
    document.querySelectorAll('.sort-ind').forEach(i => i.textContent = '');
    th.querySelector('.sort-ind').textContent = asc ? '▲' : '▼';

    const idx  = Array.from(th.parentNode.children).indexOf(th);
    const rows = Array.from(tbody.rows);

    rows.sort((a, b) => {
      const A = a.cells[idx].innerText.trim();
      const B = b.cells[idx].innerText.trim();

      // 숫자 비교 우선
      const nA = parseFloat(A.replace(/[^\d.-]/g,'')); 
      const nB = parseFloat(B.replace(/[^\d.-]/g,''));
      const bothNum = !isNaN(nA) && !isNaN(nB);
      if (bothNum) return asc ? nA - nB : nB - nA;

      // 날짜 비교(YYYY-MM-DD 등)
      const dA = Date.parse(A), dB = Date.parse(B);
      if (!isNaN(dA) && !isNaN(dB)) return asc ? dA - dB : dB - dA;

      // 문자열 비교(한글 로케일)
      return asc ? A.localeCompare(B, 'ko') : B.localeCompare(A, 'ko');
    });

    rows.forEach(r => tbody.appendChild(r));
  });
});
</script>
</body>
</html>
