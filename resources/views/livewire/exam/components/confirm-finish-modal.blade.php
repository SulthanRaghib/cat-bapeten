<div x-show="showConfirmFinishLocal" x-cloak @keydown.escape.window="showConfirmFinishLocal = false"
    style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div
        style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div
            style="background: white; width: 400px; max-width: 100%; padding: 25px; border-radius: 12px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div
                style="width: 60px; height: 60px; background: #feebc8; color: #c05621; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" style="width: 30px; height: 30px;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #1f2937;">Konfirmasi Selesai</h3>
            <p style="color: #4b5563; margin-bottom: 25px; line-height: 1.5;">Apakah Anda yakin ingin menyelesaikan
                ujian? <br>Jawaban akan dikunci dan tidak dapat diubah.</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button @click="showConfirmFinishLocal = false"
                    style="background: #e5e7eb; color: #374151; padding: 10px 20px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">Batal</button>
                <button wire:click="submitFinish" wire:loading.attr="disabled" wire:target="submitFinish"
                    @click="showConfirmFinishLocal = false"
                    style="background: #16a34a; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer;">Ya,
                    Selesai</button>
            </div>
        </div>
    </div>
</div>
