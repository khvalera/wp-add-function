(function () {
  'use strict';

  function refreshTiles(root) {
    if (!root || root.dataset.ajaxEnabled !== '1' || !window.WAF_Tiles) {
      return;
    }

    var body = root.querySelector('.waf-tiles__body');
    var status = root.querySelector('.waf-tiles__status');

    if (body) {
      body.classList.add('is-loading');
    }

    var formData = new FormData();
    formData.append('action', 'waf_tiles_render');
    formData.append('screen_id', root.dataset.screenId || '');
    formData.append('nonce', root.dataset.nonce || '');

    fetch(window.WAF_Tiles.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (!payload || !payload.success || !payload.data) {
          throw new Error('Refresh failed');
        }

        if (body) {
          body.innerHTML = payload.data.html || '';
          body.classList.remove('is-loading');
        }

        if (status) {
          status.textContent = payload.data.rendered_at ? 'Updated: ' + payload.data.rendered_at : '';
        }
      })
      .catch(function () {
        if (body) {
          body.classList.remove('is-loading');
        }
        if (status) {
          status.textContent = 'Refresh failed';
        }
      });
  }

  function initTiles(root) {
    var intervalValue = parseInt(root.dataset.refreshInterval || '0', 10);

    if (root.dataset.autoRefresh === '1' && intervalValue > 0) {
      window.setInterval(function () {
        refreshTiles(root);
      }, intervalValue * 1000);
    }

    if (root.dataset.refreshOnFocus === '1') {
      document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
          refreshTiles(root);
        }
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    var roots = document.querySelectorAll('.waf-tiles');
    roots.forEach(initTiles);
  });
})();
