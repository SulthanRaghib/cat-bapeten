document.addEventListener('alpine:init', () => {
    Alpine.data('customDateTimePicker', (config = {}) => ({
        isOpen: false,
        dropDirection: 'drop-down',
        statePath: config.statePath ?? '',
        placeholder: config.placeholder ?? '',
        locale: config.locale ?? 'id',
        pickerView: 'days', // 'days' | 'months' | 'years'

        viewMonth: new Date().getMonth(),
        viewYear: new Date().getFullYear(),

        selectedDay: null,
        selectedMonth: null,
        selectedYear: null,
        hour: 8,
        minute: 0,
        displayValue: '',

        monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        monthShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        dayNamesShort: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
        dayNamesFull: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],

        init() {
            if (this.locale === 'en') {
                this.monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                this.monthShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                this.dayNamesShort = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                this.dayNamesFull = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            }
            if (config.initialValue) this.parseValue(config.initialValue);
        },

        parseValue(val) {
            if (!val) return;
            const d = new Date(String(val).replace(' ', 'T'));
            if (isNaN(d.getTime())) return;
            this.selectedDay = d.getDate();
            this.selectedMonth = d.getMonth();
            this.selectedYear = d.getFullYear();
            this.viewMonth = d.getMonth();
            this.viewYear = d.getFullYear();
            this.hour = d.getHours();
            this.minute = d.getMinutes();
            this.refreshDisplay();
        },

        prevNav() {
            if (this.pickerView === 'days') {
                if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; }
                else this.viewMonth--;
            } else if (this.pickerView === 'years') {
                this.viewYear -= 12;
            }
        },

        nextNav() {
            if (this.pickerView === 'days') {
                if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; }
                else this.viewMonth++;
            } else if (this.pickerView === 'years') {
                this.viewYear += 12;
            }
        },

        showMonthPicker() { this.pickerView = 'months'; },
        showYearPicker() { this.pickerView = 'years'; },

        selectMonth(m) { this.viewMonth = m; this.pickerView = 'days'; },
        selectYear(y) { this.viewYear = y; this.pickerView = 'months'; },

        get yearRange() {
            const base = Math.floor(this.viewYear / 12) * 12;
            return Array.from({ length: 12 }, (_, i) => base + i);
        },

        get calendarDays() {
            const days = [];
            const totalDays = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
            let dow = new Date(this.viewYear, this.viewMonth, 1).getDay();
            dow = dow === 0 ? 6 : dow - 1;
            const today = new Date();
            for (let i = 0; i < dow; i++) days.push({ day: null, isSelected: false, isToday: false });
            for (let d = 1; d <= totalDays; d++) {
                days.push({
                    day: d,
                    isSelected: this.selectedDay === d && this.selectedMonth === this.viewMonth && this.selectedYear === this.viewYear,
                    isToday: today.getDate() === d && today.getMonth() === this.viewMonth && today.getFullYear() === this.viewYear,
                });
            }
            return days;
        },

        selectDay(day) {
            this.selectedDay = day;
            this.selectedMonth = this.viewMonth;
            this.selectedYear = this.viewYear;
        },

        setNow() {
            const n = new Date();
            this.selectedDay = n.getDate();
            this.selectedMonth = n.getMonth();
            this.selectedYear = n.getFullYear();
            this.viewMonth = n.getMonth();
            this.viewYear = n.getFullYear();
            this.hour = n.getHours();
            this.minute = n.getMinutes();
        },

        clampHour(v) { this.hour = Math.min(23, Math.max(0, parseInt(v) || 0)); },
        clampMinute(v) { this.minute = Math.min(59, Math.max(0, parseInt(v) || 0)); },

        confirm() {
            if (this.selectedDay === null) return;
            const y = this.selectedYear;
            const m = String(this.selectedMonth + 1).padStart(2, '0');
            const d = String(this.selectedDay).padStart(2, '0');
            const h = String(this.hour).padStart(2, '0');
            const min = String(this.minute).padStart(2, '0');
            this.$wire.set(this.statePath, `${y}-${m}-${d} ${h}:${min}:00`);
            this.refreshDisplay();
            this.isOpen = false;
        },

        clear() {
            this.selectedDay = this.selectedMonth = this.selectedYear = null;
            this.hour = 8; this.minute = 0;
            this.displayValue = '';
            this.$wire.set(this.statePath, null);
            this.isOpen = false;
        },

        refreshDisplay() {
            if (this.selectedDay === null) { this.displayValue = ''; return; }
            const jsDate = new Date(this.selectedYear, this.selectedMonth, this.selectedDay);
            const dayName = this.dayNamesFull[jsDate.getDay()];
            const h = String(this.hour).padStart(2, '0');
            const min = String(this.minute).padStart(2, '0');
            this.displayValue = `${dayName}, ${this.selectedDay} ${this.monthNames[this.selectedMonth]} ${this.selectedYear} \u00B7 ${h}:${min}`;
        },

        pad: (n) => String(n).padStart(2, '0'),
    }));
});
