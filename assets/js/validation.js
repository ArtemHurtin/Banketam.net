// validation.js
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) return;
        
        this.fields = this.form.querySelectorAll('[data-validate]');
        this.init();
    }
    
    init() {
        this.fields.forEach(field => {
            field.addEventListener('input', () => this.validateField(field));
            field.addEventListener('blur', () => this.validateField(field));
        });
        
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }
    
    validateField(field) {
        const rules = field.dataset.validate.split(' ');
        let isValid = true;
        let errorMessage = '';
        
        rules.forEach(rule => {
            const [ruleName, ruleValue] = rule.split(':');
            
            switch(ruleName) {
                case 'required':
                    if (!field.value.trim()) {
                        isValid = false;
                        errorMessage = 'Обязательное поле';
                    }
                    break;
                    
                case 'minlength':
                    if (field.value.length < parseInt(ruleValue)) {
                        isValid = false;
                        errorMessage = `Минимум ${ruleValue} символов`;
                    }
                    break;
                    
                case 'login':
                    const loginRegex = /^[a-zA-Z0-9]{6,}$/;
                    if (!loginRegex.test(field.value)) {
                        isValid = false;
                        errorMessage = 'Только латинские буквы и цифры, мин. 6 символов';
                    }
                    break;
                    
                case 'password':
                    if (field.value.length < 8) {
                        isValid = false;
                        errorMessage = 'Пароль должен быть не менее 8 символов';
                    }
                    break;
                    
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(field.value)) {
                        isValid = false;
                        errorMessage = 'Неверный формат email';
                    }
                    break;
                    
                case 'phone':
                    const phoneRegex = /^\+?[0-9]{10,15}$/;
                    if (!phoneRegex.test(field.value.replace(/[\s\(\)\-]/g, ''))) {
                        isValid = false;
                        errorMessage = 'Неверный формат телефона';
                    }
                    break;
            }
        });
        
        this.showFieldError(field, isValid ? '' : errorMessage);
        return isValid;
    }
    
    showFieldError(field, message) {
        const errorDiv = field.nextElementSibling?.classList.contains('error-message') 
            ? field.nextElementSibling 
            : document.createElement('div');
        
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        
        if (!field.nextElementSibling?.classList.contains('error-message')) {
            field.parentNode.insertBefore(errorDiv, field.nextSibling);
        }
        
        field.classList.toggle('error', !!message);
    }
    
    handleSubmit(e) {
        e.preventDefault();
        let isValid = true;
        
        this.fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        if (isValid) {
            // Отправка формы
            this.form.submit();
        }
    }
}

// Инициализация форм
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('register-form')) {
        new FormValidator('register-form');
    }
    if (document.getElementById('login-form')) {
        new FormValidator('login-form');
    }
    if (document.getElementById('booking-form')) {
        new FormValidator('booking-form');
    }
});