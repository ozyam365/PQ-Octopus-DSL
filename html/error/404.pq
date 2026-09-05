<style>
    .pq-error-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 55vh;
        text-align: center;
        padding: 40px 20px;
    }
    .pq-error-code {
        font-size: 7.5rem;
        font-weight: 900;
        color: #2563eb;
        line-height: 1;
        margin: 0;
        letter-spacing: -2px;
        text-shadow: 3px 3px 0px #dbeafe;
    }
    .pq-error-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin: 20px 0 10px;
    }
    .pq-error-desc {
        font-size: 1rem;
        color: #64748b;
        margin-bottom: 35px;
        line-height: 1.6;
    }
    .pq-error-btn-group {
        display: flex;
        gap: 12px;
        justify-content: center;
    }
    .pq-error-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 28px;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .pq-error-btn-primary {
        background-color: #2563eb;
        color: #ffffff;
        border: 1px solid #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }
    .pq-error-btn-primary:hover {
        background-color: #1d4ed8;
        transform: translateY(-2px);
    }
    .pq-error-btn-secondary {
        background-color: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .pq-error-btn-secondary:hover {
        background-color: #f8fafc;
        color: #1e293b;
    }
</style>

<main class="pq-error-container">
    <!-- 404 대형 텍스트 -->
    <div class="pq-error-code">404</div>
    
    <h1 class="pq-error-title">길을 잃으셨나요? 페이지를 찾을 수 없습니다!</h1>
    
    <p class="pq-error-desc">
        요청하신 페이지가 삭제되었거나, 주소가 변경되었을 수 있습니다.<br>
        입력하신 주소가 정확한지 다시 한번 확인해 주세요.
    </p>

    <!-- 이동 버튼 그룹 -->
    <div class="pq-error-btn-group">
        <a href="javascript:history.back();" class="pq-error-btn pq-error-btn-secondary">
            ◀ 이전 페이지
        </a>
        <a href="/index" class="pq-error-btn pq-error-btn-primary">
            🏠 메인으로 이동
        </a>
    </div>
</main>