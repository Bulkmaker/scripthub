<template>
    <div class="field-renderer">
        <div
            v-for="field in visibleFields"
            :key="field.key"
            style="margin-bottom:1.25rem"
        >
            <label style="display:block;font-weight:500;margin-bottom:0.375rem;font-size:0.875rem">
                {{ field.label }}
                <span v-if="field.required" style="color:var(--p-red-500)">*</span>
            </label>

            <!-- Text -->
            <InputText
                v-if="field.type === 'text'"
                v-model="config[field.key]"
                :placeholder="field.placeholder || ''"
                style="width:100%"
                :invalid="!!errors[field.key]"
            />

            <!-- Number -->
            <InputNumber
                v-else-if="field.type === 'number'"
                v-model="config[field.key]"
                :placeholder="field.placeholder || ''"
                style="width:100%"
                :useGrouping="false"
            />

            <!-- Toggle -->
            <div v-else-if="field.type === 'toggle'" style="display:flex;align-items:center;gap:0.5rem">
                <ToggleSwitch v-model="config[field.key]" />
            </div>

            <!-- Select -->
            <Select
                v-else-if="field.type === 'select'"
                v-model="config[field.key]"
                :options="field.options || []"
                optionLabel="label"
                optionValue="value"
                :placeholder="field.placeholder || 'Выберите...'"
                style="width:100%"
            />

            <!-- Textarea -->
            <Textarea
                v-else-if="field.type === 'textarea'"
                v-model="config[field.key]"
                :placeholder="field.placeholder || ''"
                rows="4"
                style="width:100%"
            />

            <!-- Code -->
            <Textarea
                v-else-if="field.type === 'code'"
                v-model="config[field.key]"
                rows="6"
                style="width:100%;font-family:monospace;font-size:0.8rem"
            />

            <!-- Password -->
            <Password
                v-else-if="field.type === 'password'"
                v-model="config[field.key]"
                :feedback="false"
                toggleMask
                style="width:100%"
            />

            <small v-if="field.helpText" style="display:block;margin-top:0.25rem;color:var(--p-text-muted-color)">
                {{ field.helpText }}
            </small>
            <small v-if="errors[field.key]" style="display:block;margin-top:0.25rem;color:var(--p-red-500)">
                {{ errors[field.key] }}
            </small>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { InputText, InputNumber, ToggleSwitch, Select, Textarea, Password } from 'primevue'

const props = defineProps({
    fields: { type: Array, required: true },
    config: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) },
})

const visibleFields = computed(() => {
    return props.fields.filter(field => {
        if (!field.showIf) return true
        const [depKey, depValue] = field.showIf
        return props.config[depKey] === depValue
    })
})
</script>
