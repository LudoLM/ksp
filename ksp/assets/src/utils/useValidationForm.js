
export async function useValidationForm(response, errors) {
    const message = await response.json();
    if (message.detail) {
        errors.value = message.detail.split('\n').reduce((acc, error) => {
            const [key, msg] = error.split(': ');
            acc[key] = msg;
            return acc;
        }, {});
    } else {
        errors.value = {};
        const [key, value] = Object.entries(message["errors"][0])[0];
        errors.value[key] = value;
    }
}
