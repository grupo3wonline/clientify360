/**
 * Alpine component for CPF/CNPJ document input.
 *
 * Reacts to the selected person type, formats the document value on input,
 * and keeps the value masked when the form re-renders (e.g. after a
 * validation error). Register once via `Alpine.data('documentField', ...)`.
 *
 * Usage:
 *   <div x-data="documentField('{{ old('person_type', $type) }}', '{{ old('document', $doc) }}')">
 *     <select x-model="personType">...</select>
 *     <input x-model="document" :placeholder="placeholder" :maxlength="maxLength" @input="format()">
 *   </div>
 */
export default function documentField(personType = 'individual', document = '') {
    return {
        personType,
        document: formatDocument(document, personType),

        get isCompany() {
            return this.personType === 'company';
        },

        get label() {
            return this.isCompany ? 'CNPJ' : 'CPF';
        },

        get placeholder() {
            return this.isCompany ? '00.000.000/0000-00' : '000.000.000-00';
        },

        get maxLength() {
            return this.isCompany ? 18 : 14;
        },

        /** Re-mask the current value (bind to the input's @input event). */
        format() {
            this.document = formatDocument(this.document, this.personType);
        },
    };
}

/** Strip non-digits and apply the CPF or CNPJ mask based on person type. */
function formatDocument(value, personType) {
    let digits = String(value ?? '').replace(/\D/g, '');

    if (personType === 'company') {
        digits = digits.slice(0, 14);

        return digits
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2');
    }

    digits = digits.slice(0, 11);

    return digits
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
}
