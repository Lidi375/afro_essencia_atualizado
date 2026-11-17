    </main><!-- /adm-main -->
</div><!-- /adm-layout -->

<?php if (!empty($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?= htmlspecialchars($js ?? '', ENT_QUOTES) ?>" defer></script>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    lucide.createIcons();
</script>

<script src="/afro_essencia/public/js/global/app.js"></script>
</body>
</html>
