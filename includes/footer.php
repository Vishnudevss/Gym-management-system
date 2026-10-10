        </section>
        <!-- End page-content -->

    </main>
    <!-- End main-content -->

</div>
<!-- End app-layout -->


<!-- Load jQuery first -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Load Bootstrap after jQuery -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Common JavaScript -->
<script src="assets/js/common.js"></script>

<!-- Page-specific JavaScript -->
<?php if (!empty($pageScript)): ?>
    <script src="<?= htmlspecialchars($pageScript, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endif; ?>

</body>
</html>