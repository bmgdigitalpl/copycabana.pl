(function () {
  const START_DELAY_MS = 5000;
  const SCROLL_SPEED_PX_PER_SECOND = 90;
  const TOP_SCROLL_DURATION_MS = 900;

  let startTimer = null;
  let animationFrame = null;
  let topScrollTimer = null;
  let lastFrameTime = null;
  let isActive = false;

  function isTypingTarget(target) {
    if (!target) return false;
    const tagName = target.tagName ? target.tagName.toLowerCase() : '';
    return (
      tagName === 'input' ||
      tagName === 'textarea' ||
      tagName === 'select' ||
      target.isContentEditable
    );
  }

  function getMaxScrollY() {
    return Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
  }

  function clearTimers() {
    if (startTimer) {
      window.clearTimeout(startTimer);
      startTimer = null;
    }

    if (animationFrame) {
      window.cancelAnimationFrame(animationFrame);
      animationFrame = null;
    }

    if (topScrollTimer) {
      window.clearTimeout(topScrollTimer);
      topScrollTimer = null;
    }

    lastFrameTime = null;
  }

  function stopPresentation() {
    isActive = false;
    clearTimers();
  }

  function enterFullscreen() {
    if (document.fullscreenElement || !document.documentElement.requestFullscreen) {
      return;
    }

    document.documentElement.requestFullscreen().catch(() => {});
  }

  function scrollDown(timestamp) {
    if (!isActive) return;

    if (lastFrameTime === null) {
      lastFrameTime = timestamp;
    }

    const deltaSeconds = (timestamp - lastFrameTime) / 1000;
    lastFrameTime = timestamp;

    const nextY = Math.min(
      window.scrollY + SCROLL_SPEED_PX_PER_SECOND * deltaSeconds,
      getMaxScrollY()
    );

    window.scrollTo(0, nextY);

    if (window.scrollY >= getMaxScrollY() - 1) {
      stopPresentation();
      return;
    }

    animationFrame = window.requestAnimationFrame(scrollDown);
  }

  function startScrollAfterDelay() {
    if (!isActive) return;

    startTimer = window.setTimeout(() => {
      startTimer = null;
      lastFrameTime = null;
      animationFrame = window.requestAnimationFrame(scrollDown);
    }, START_DELAY_MS);
  }

  function restartFromTop() {
    isActive = true;
    clearTimers();

    window.scrollTo({ top: 0, behavior: 'smooth' });
    topScrollTimer = window.setTimeout(() => {
      topScrollTimer = null;
      startScrollAfterDelay();
    }, TOP_SCROLL_DURATION_MS);
  }

  function startPresentation() {
    isActive = true;
    clearTimers();
    startScrollAfterDelay();
  }

  document.addEventListener('keydown', (event) => {
    if (isTypingTarget(event.target) || event.altKey || event.ctrlKey || event.metaKey) {
      return;
    }

    if (event.key === 'Escape') {
      stopPresentation();
      return;
    }

    if (event.key.toLowerCase() !== 'p') {
      return;
    }

    event.preventDefault();
    enterFullscreen();

    if (isActive || window.scrollY > 0) {
      restartFromTop();
    } else {
      startPresentation();
    }
  });
})();
