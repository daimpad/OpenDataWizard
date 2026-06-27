// Common filters for usage across the application

export default {
    filters: {
      stripHtml(value: string): string {
        // Strips content from HTML tags
        const div = document.createElement('div');
        div.innerHTML = value;
        const text = div.textContent || div.innerText || '';
        return text;
      },
    },
  };
  