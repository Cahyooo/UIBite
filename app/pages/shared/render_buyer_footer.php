<?php

function render_buyer_footer(): void
    {
        ?>
        </main>
        <footer class="mt-16 border-t border-slate-200 bg-white">
            <div class="container-app flex flex-col justify-between gap-4 py-8 text-sm text-slate-500 sm:flex-row sm:items-center">
                <div class="flex items-center gap-2"><span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-600 text-white"><i data-lucide="utensils" class="h-4 w-4"></i></span><span class="font-bold text-slate-700">UIBite</span><span>· Campus food, made simple.</span></div>
                <p>Project UAS Pemrograman Basis Data · Demo akademik</p>
            </div>
        </footer>
        <script src="<?= e(asset('js/app.js')) ?>"></script>
    </body>

    </html>
<?php
    }
