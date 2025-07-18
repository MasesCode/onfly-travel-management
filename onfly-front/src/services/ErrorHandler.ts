export interface ValidationError {
  field: string;
  message: string;
}

export interface ErrorResponse {
  message: string;
  errors?: { [key: string]: string[] };
  status: number;
}

export class ErrorHandler {
  static handleError(error: unknown): { message: string; validationErrors: ValidationError[] } {
    console.error('Erro capturado:', error);

    const axiosError = error as { response?: { status?: number; data?: { message?: string; errors?: { [key: string]: string[] } } } };

    if (!axiosError.response) {
      return {
        message: 'Erro de conexão. Verifique sua internet e tente novamente.',
        validationErrors: []
      };
    }

    const status = axiosError.response.status;
    const data = axiosError.response.data;

    switch (status) {
      case 400:
        return {
          message: data?.message || 'Dados inválidos. Verifique as informações enviadas.',
          validationErrors: this.extractValidationErrors(data?.errors)
        };

      case 401:
        return {
          message: 'Sua sessão expirou. Faça login novamente.',
          validationErrors: []
        };

      case 403:
        return {
          message: data?.message || 'Você não tem permissão para realizar esta ação.',
          validationErrors: []
        };

      case 404:
        return {
          message: 'Recurso não encontrado.',
          validationErrors: []
        };

      case 422: {
        const validationErrors = this.extractValidationErrors(data?.errors);
        return {
          message: validationErrors.length > 0
            ? 'Por favor, corrija os erros abaixo:'
            : data?.message || 'Dados inválidos.',
          validationErrors
        };
      }

      case 429:
        return {
          message: 'Muitas tentativas. Aguarde um momento antes de tentar novamente.',
          validationErrors: []
        };

      case 500:
        return {
          message: 'Erro interno do servidor. Nossa equipe foi notificada.',
          validationErrors: []
        };

      case 502:
      case 503:
      case 504:
        return {
          message: 'Serviço temporariamente indisponível. Tente novamente em alguns minutos.',
          validationErrors: []
        };

      default:
        return {
          message: data?.message || 'Ocorreu um erro inesperado. Tente novamente.',
          validationErrors: []
        };
    }
  }

  /**
   * Extrai erros de validação do formato Laravel
   */
  private static extractValidationErrors(errors?: { [key: string]: string[] }): ValidationError[] {
    if (!errors) return [];

    const validationErrors: ValidationError[] = [];

    for (const [field, messages] of Object.entries(errors)) {
      const friendlyField = this.getFriendlyFieldName(field);

      messages.forEach(message => {
        validationErrors.push({
          field: friendlyField,
          message: this.translateValidationMessage(message, friendlyField)
        });
      });
    }

    return validationErrors;
  }

  /**
   * Converte nomes de campos para nomes amigáveis em português
   */
  private static getFriendlyFieldName(field: string): string {
    const fieldNames: { [key: string]: string } = {
      'name': 'Nome',
      'email': 'E-mail',
      'password': 'Senha',
      'password_confirmation': 'Confirmação de senha',
      'destination': 'Destino',
      'departure_date': 'Data de ida',
      'return_date': 'Data de volta',
      'user_id': 'Usuário',
      'requester_name': 'Nome do solicitante',
      'is_admin': 'Administrador'
    };

    return fieldNames[field] || field;
  }

  private static translateValidationMessage(message: string, fieldName: string): string {
    const translations: { [key: string]: string } = {
      'required': `${fieldName} é obrigatório.`,
      'email': `${fieldName} deve ser um e-mail válido.`,
      'min': `${fieldName} deve ter pelo menos :min caracteres.`,
      'max': `${fieldName} não pode ter mais de :max caracteres.`,
      'unique': `${fieldName} já está sendo usado.`,
      'confirmed': `${fieldName} não confere.`,
      'date': `${fieldName} deve ser uma data válida.`,
      'after_or_equal': `${fieldName} deve ser uma data igual ou posterior.`,
      'exists': `${fieldName} selecionado é inválido.`,
      'in': `${fieldName} selecionado é inválido.`,
      'numeric': `${fieldName} deve ser um número.`,
      'integer': `${fieldName} deve ser um número inteiro.`,
      'boolean': `${fieldName} deve ser verdadeiro ou falso.`,
      'string': `${fieldName} deve ser um texto.`,
      'array': `${fieldName} deve ser uma lista.`,
      'file': `${fieldName} deve ser um arquivo.`,
      'image': `${fieldName} deve ser uma imagem.`,
      'mimes': `${fieldName} deve ser um arquivo do tipo: :values.`,
      'max_file': `${fieldName} não pode ser maior que :max KB.`,
      'regex': `${fieldName} tem formato inválido.`
    };

    for (const [pattern, translation] of Object.entries(translations)) {
      if (message.toLowerCase().includes(pattern)) {
        return translation;
      }
    }

    if (message.includes('after_or_equal:today')) {
      return `${fieldName} não pode ser anterior à data de hoje.`;
    }

    if (message.includes('after_or_equal:departure_date')) {
      return 'Data de volta deve ser igual ou posterior à data de ida.';
    }

    // Fallback: traduzir mensagens comuns do Laravel
    if (message.includes('The destination field is required')) {
      return 'O campo Destino é obrigatório.';
    }

    if (message.includes('The departure_date field is required')) {
      return 'O campo Data de ida é obrigatório.';
    }

    if (message.includes('The return_date field is required')) {
      return 'O campo Data de volta é obrigatório.';
    }

    return message;
  }
}
