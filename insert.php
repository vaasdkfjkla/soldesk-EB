<!doctype html>
<html lang="ko" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>신규 회원 입력</title>
  <style>
    :root{
      --bg:#0e1229; --card:#151b3a; --text:#eaf0ff; --muted:#9aa3c7;
      --accent:#7ca6ff; --accent2:#8df3ff; --ok:#29d398; --warn:#ffd166; --danger:#ff6b6b;
      --border:rgba(255,255,255,.12); --input:#0d1231; --shadow:0 14px 40px rgba(0,0,0,.35);
    }
    [data-theme="light"]{
      --bg:#f2f5ff; --card:#ffffff; --text:#1a1f36; --muted:#57607a;
      --accent:#4c7dff; --accent2:#3bd6ff; --ok:#10b981; --warn:#f59e0b; --danger:#ef4444;
      --border:rgba(10,20,60,.15); --input:#f8faff; --shadow:0 10px 24px rgba(22,27,79,.1);
    }
    *{box-sizing:border-box}
    body{
      margin:0; background:
        radial-gradient(1000px 500px at 15% -10%, rgba(124,166,255,.18), transparent 60%),
        radial-gradient(900px 600px at 110% 0%, rgba(141,243,255,.18), transparent 60%),
        var(--bg);
      color:var(--text); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Noto Sans KR', Pretendard, sans-serif;
      min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
    }
    .card{
      width:min(920px, 100%); background:
        linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.01)),
        var(--card);
      border:1px solid var(--border); border-radius:22px; box-shadow:var(--shadow); overflow:hidden;
    }
    .head{
      display:flex; gap:12px; align-items:center; justify-content:space-between; padding:20px 22px 0;
    }
    .head h1{margin:0; font-size:22px; letter-spacing:.2px}
    .badge{
      display:inline-flex; gap:8px; align-items:center; padding:8px 12px;
      background:linear-gradient(90deg,var(--accent),var(--accent2));
      border-radius:999px; color:#0c1233; font-weight:800;
    }
    .tools{display:flex; gap:10px; align-items:center}
    .btn{
      appearance:none; border:1px solid var(--border); background:transparent; color:var(--text);
      padding:10px 14px; border-radius:12px; cursor:pointer; font-weight:700;
    }
    .btn:hover{filter:brightness(1.08)}
    .wrap{padding:20px 22px 22px}
    .grid{
      display:grid; grid-template-columns:repeat(2,1fr); gap:16px;
    }
    @media (max-width:760px){ .grid{grid-template-columns:1fr} }
    .field{
      background:var(--input); border:1px solid var(--border); border-radius:14px; padding:12px 12px 10px; position:relative;
    }
    .label{
      display:block; font-size:12px; color:var(--muted); margin-bottom:6px; letter-spacing:.3px;
    }
    .input{
      width:100%; background:transparent; border:none; outline:none; color:var(--text);
      font-size:16px; padding:6px 0 4px;
    }
    .side{
      position:absolute; right:10px; top:10px; font-size:16px; opacity:.75;
    }
    .hint{font-size:12px; color:var(--muted); margin-top:6px}
    .hint.ok{color:var(--ok)}
    .hint.warn{color:var(--warn)}
    .hint.err{color:var(--danger)}
    .row{display:flex; gap:12px}
    .row .field{flex:1}
    .footer{
      display:flex; flex-wrap:wrap; gap:10px; justify-content:space-between; align-items:center; margin-top:18px;
    }
    .submit{
      background:linear-gradient(90deg,var(--accent),var(--accent2)); border:none; color:#0c1233;
      padding:12px 18px; border-radius:12px; font-weight:900; cursor:pointer;
    }
    .submit[disabled]{opacity:.6; cursor:not-allowed}
    .link{color:var(--muted); text-decoration:none; border:1px solid var(--border); padding:10px 12px; border-radius:10px}
    .progress{
      height:10px; background:rgba(255,255,255,.06); border:1px solid var(--border); border-radius:999px; overflow:hidden;
    }
    .bar{height:100%; width:0%; background:linear-gradient(90deg,var(--ok),var(--accent)); transition:width .25s ease}
    .sr{position:absolute; left:-9999px}
  </style>
</head>
<body>
  <div class="card">
    <div class="head">
      <h1>✨ 신규 회원 입력</h1>
      <div class="tools">
        <span class="badge">🧾 신규 등록</span>
        <button type="button" class="btn" id="toggle-theme">🌗 테마 전환</button>
      </div>
    </div>

    <form class="wrap" method="post" action="insert_result.php" id="memberForm" novalidate>
      <!-- 진행도 -->
      <div class="progress" aria-hidden="true"><div class="bar" id="bar"></div></div>
      <span id="live" class="sr" aria-live="polite"></span>

      <div class="grid" style="margin-top:14px">
        <!-- 아이디 -->
        <div class="field" data-field="userID">
          <label class="label" for="userID">👤 아이디</label>
          <input class="input" id="userID" name="userID" type="text" placeholder="영문/숫자/밑줄, 4~20자"
                 autocomplete="username" required pattern="^[A-Za-z0-9_]{4,20}$" maxlength="20">
          <span class="side" data-icon>🔎</span>
          <div class="hint">영문/숫자/밑줄만 사용</div>
        </div>

        <!-- 이름 -->
        <div class="field" data-field="name">
          <label class="label" for="name">🧑 이름</label>
          <input class="input" id="name" name="name" type="text" placeholder="예: 홍길동"
                 autocomplete="name" required maxlength="40">
          <span class="side" data-icon>✍️</span>
          <div class="hint">실명을 입력해주세요</div>
        </div>

        <!-- 출생년도 -->
        <div class="field" data-field="birthYear">
          <label class="label" for="birthYear">🎂 출생년도</label>
          <input class="input" id="birthYear" name="birthYear" type="number" placeholder="예: 1990"
                 inputmode="numeric" min="1920" max="2025" required>
          <span class="side" data-icon>📅</span>
          <div class="hint">YYYY 형식</div>
        </div>

        <!-- 지역 -->
        <div class="field" data-field="addr">
          <label class="label" for="addr">🗺️ 지역</label>
          <input class="input" id="addr" name="addr" list="regions" placeholder="예: 서울특별시" required>
          <datalist id="regions">
            <option value="서울특별시"><option value="부산광역시"><option value="대구광역시">
            <option value="인천광역시"><option value="광주광역시"><option value="대전광역시">
            <option value="울산광역시"><option value="세종특별자치시"><option value="경기도">
            <option value="강원특별자치도"><option value="충청북도"><option value="충청남도">
            <option value="전북특별자치도"><option value="전라남도"><option value="경상북도">
            <option value="경상남도"><option value="제주특별자치도">
          </datalist>
          <span class="side" data-icon>📍</span>
          <div class="hint">도시/도 선택 또는 직접 입력</div>
        </div>

        <!-- 휴대폰 국번 -->
        <div class="field" data-field="mobile1">
          <label class="label" for="mobile1">☎️ 휴대폰 국번</label>
          <input class="input" id="mobile1" name="mobile1" list="mobilePrefix" placeholder="예: 010"
                 inputmode="numeric" required pattern="^01[016789]$" maxlength="3">
          <datalist id="mobilePrefix">
            <option value="010"><option value="011"><option value="016"><option value="017"><option value="018"><option value="019">
          </datalist>
          <span class="side" data-icon>📞</span>
          <div class="hint">예: 010</div>
        </div>

        <!-- 휴대폰 번호 -->
        <div class="field" data-field="mobile2">
          <label class="label" for="mobile2">📞 휴대폰 전화번호</label>
          <input class="input" id="mobile2" name="mobile2" type="text" placeholder="예: 12345678"
                 inputmode="numeric" required pattern="^[0-9]{7,8}$" maxlength="8">
          <span class="side" data-icon>🔢</span>
          <div class="hint">하이픈 없이 7~8자리</div>
        </div>

        <!-- 신장 -->
        <div class="field" data-field="height">
          <label class="label" for="height">📏 신장(cm)</label>
          <input class="input" id="height" name="height" type="number" placeholder="예: 175"
                 inputmode="numeric" min="50" max="250" step="1" required>
          <span class="side" data-icon>📐</span>
          <div class="hint">50 ~ 250 사이</div>
        </div>

        <!-- 안내 영역 (좌우 폭 맞춤용 빈 칸) -->
        <div class="field" style="display:flex; align-items:center; justify-content:center; min-height:74px;">
          <div class="hint">⏎ Enter로 제출 • Tab으로 이동 • 필수값은 모두 작성</div>
        </div>
      </div>

      <div class="footer">
        <a href="main.html" class="link">🏠 초기 화면</a>
        <div class="row">
          <button type="reset" class="btn">🧹 초기화</button>
          <button type="submit" class="submit" id="submitBtn">🚀 회원 입력</button>
        </div>
      </div>
    </form>
  </div>

  <script>
    // 🌗 테마 토글
    const root = document.documentElement;
    document.getElementById('toggle-theme').addEventListener('click', () => {
      root.dataset.theme = (root.dataset.theme === 'light') ? 'dark' : 'light';
    });

    // ✅ 실시간 유효성 검사 + 진행도 바 + 메시지
    const form = document.getElementById('memberForm');
    const bar  = document.getElementById('bar');
    const live = document.getElementById('live');
    const submitBtn = document.getElementById('submitBtn');

    const fields = Array.from(form.querySelectorAll('.field [name]'));
    const total  = fields.length;

    function onlyDigits(el){ el.value = el.value.replace(/[^\d]/g, ''); }

    // 숫자 전용 보조
    ['birthYear','mobile1','mobile2','height'].forEach(id=>{
      const el = document.getElementById(id);
      el.addEventListener('input', ()=>onlyDigits(el));
    });

    function setHint(el, type, msg){
      const box = el.closest('.field');
      const hint = box.querySelector('.hint');
      const icon = box.querySelector('[data-icon]');
      hint.classList.remove('ok','warn','err');
      if(type==='ok'){ hint.classList.add('ok'); icon.textContent='✅'; }
      else if(type==='warn'){ hint.classList.add('warn'); icon.textContent='⚠️'; }
      else if(type==='err'){ hint.classList.add('err'); icon.textContent='⛔'; }
      hint.textContent = msg;
    }

    function validate(el){
      if(!el.value.trim()){ setHint(el,'warn','값을 입력해주세요'); return false; }
      if(!el.checkValidity()){
        // 필드별 맞춤 메시지
        switch(el.name){
          case 'userID': setHint(el,'err','아이디: 영문/숫자/밑줄 4~20자'); break;
          case 'birthYear': setHint(el,'err','출생년도: 1920~2025 사이의 숫자'); break;
          case 'addr': setHint(el,'err','지역을 선택/입력해주세요'); break;
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
      const validCount = fields.reduce((n,el)=> n + (validate(el)?1:0), 0);
      bar.style.width = `${Math.round(validCount/total*100)}%`;
      live.textContent = `완료도 ${Math.round(validCount/total*100)}%`;
    }

    fields.forEach(el=>{
      el.addEventListener('input', refreshProgress);
      el.addEventListener('blur',  ()=>validate(el));
    });
    refreshProgress();

    // ⛔ 중복 제출 방지 + 마지막 검증
    form.addEventListener('submit', (e)=>{
      let allOk = true;
      fields.forEach(el => { if(!validate(el)) allOk=false; });
      if(!allOk){
        e.preventDefault();
        live.textContent = '필수 항목을 다시 확인해주세요.';
        return;
      }
      submitBtn.disabled = true;
      submitBtn.textContent = '⏳ 전송 중...';
    });
  </script>
</body>
</html>
