/**
 * DynamicCRUD - Client-side Validation
 */

class DynamicCRUDValidator {
    constructor(formSelector = '.dynamic-crud-form') {
        this.form = document.querySelector(formSelector);
        if (!this.form) return;
        
        this.init();
    }

    init() {
        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => {
                const value = field.value.trim();
                this.clearError(field);
                
                // Validar maxlength en tiempo real
                if (field.maxLength > 0 && value.length >= field.maxLength) {
                    this.showError(field, `Máximo ${field.maxLength} caracteres`);
                }
                
                // Validar minlength en tiempo real
                if (field.minLength > 0 && value.length > 0 && value.length < field.minLength) {
                    this.showError(field, `Mínimo ${field.minLength} caracteres`);
                }
                
                // Validar min/max para números
                if (field.type === 'number' && value) {
                    const num = parseFloat(value);
                    if (!isNaN(num)) {
                        if (field.min && num < parseFloat(field.min)) {
                            this.showError(field, `Debe ser mayor o igual a ${field.min}`);
                        }
                        if (field.max && num > parseFloat(field.max)) {
                            this.showError(field, `Debe ser menor o igual a ${field.max}`);
                        }
                    }
                }
            });
        });

        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                return;
            }
            
            // Mostrar spinner y deshabilitar formulario
            this.showLoading();
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const errors = [];

        if (field.hasAttribute('required') && !value) {
            errors.push('Este campo es requerido');
        }

        if (value) {
            if (field.type === 'email') {
                if (!this.isValidEmail(value)) {
                    errors.push('Debe ser un email válido');
                }
            }

            if (field.type === 'url') {
                if (!this.isValidUrl(value)) {
                    errors.push('Debe ser una URL válida');
                }
            }

            if (field.type === 'number') {
                const num = parseFloat(value);
                if (isNaN(num)) {
                    errors.push('Debe ser un número válido');
                } else {
                    if (field.min && num < parseFloat(field.min)) {
                        errors.push(`Debe ser mayor o igual a ${field.min}`);
                    }
                    if (field.max && num > parseFloat(field.max)) {
                        errors.push(`Debe ser menor o igual a ${field.max}`);
                    }
                }
            }

            if (field.maxLength > 0 && value.length > field.maxLength) {
                errors.push(`Máximo ${field.maxLength} caracteres`);
            }
            
            if (field.minLength > 0 && value.length < field.minLength) {
                errors.push(`Mínimo ${field.minLength} caracteres`);
            }
        }

        if (errors.length > 0) {
            this.showError(field, errors[0]);
            return false;
        } else {
            this.clearError(field);
            return true;
        }
    }

    validateForm() {
        let isValid = true;
        
        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    showError(field, message) {
        this.clearError(field);
        
        field.classList.add('error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        
        field.parentElement.appendChild(errorDiv);
    }

    clearError(field) {
        field.classList.remove('error');
        
        const errorDiv = field.parentElement.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidUrl(url) {
        // Verificar que comience con http:// o https://
        if (!/^https?:\/\//i.test(url)) {
            return false;
        }
        
        try {
            const urlObj = new URL(url);
            // Verificar que tenga un dominio válido
            return urlObj.hostname.includes('.');
        } catch {
            return false;
        }
    }
    
    showLoading() {
        const submitBtn = this.form.querySelector('button[type="submit"]');
        if (!submitBtn) return;
        
        // Deshabilitar botón y formulario
        submitBtn.disabled = true;
        this.form.classList.add('form-loading');
        
        // Añadir spinner
        const spinner = document.createElement('span');
        spinner.className = 'spinner';
        spinner.setAttribute('role', 'status');
        spinner.setAttribute('aria-label', 'Cargando...');
        submitBtn.appendChild(spinner);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new DynamicCRUDValidator();
    });
} else {
    new DynamicCRUDValidator();
}
