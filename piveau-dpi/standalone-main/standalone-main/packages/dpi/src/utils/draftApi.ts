import Axios from 'axios';

const createDraftApi = ({ baseURL, authToken }: { baseURL: string, authToken: string }) => {
  const maybeTrailingSlash = baseURL.endsWith('/') ? '' : '/';
  const api = Axios.create({
    baseURL: `${baseURL}${maybeTrailingSlash}drafts`,
    headers: {
      'Content-Type': 'text/turtle',
      Authorization: `Bearer ${authToken}`,
    },
  });

  // Create a new draft
  const createDatasetDraft = ({ id, catalogue, body }: { id: string, catalogue: string, body: unknown}) =>
    api.put(`/datasets/${id}`, body, {
    headers: { 'Content-Type': 'application/json' },
    params: { catalogue },
  });

  // Get all drafts
  const getAllDatasetDrafts = () => api.get('/datasets');

  // Get a draft by id or all drafts associated to current authorized user
  const getDatasetDrafts = ({ id = '', catalogue = '', filterByProvider = false }) => {
    const maybeId = id ? `/${id}` : '';
    return api.get(`/datasets${maybeId}`, { params: { catalogue, filterByProvider } });
  };

  // Delete a draft by id
  const deleteDatasetDraft = ({ id, catalogue }: { id: string, catalogue: string }) =>
    api.delete(`/datasets/${id}`, { params: { catalogue } });

  // Publish a draft as dataset
  const publishDatasetDraft = ({ id, catalogue, body }: { id: string, catalogue: string, body: unknown }) => {
    const maybeId = id ? `/${id}` : '';
    return api.put(`/datasets/publish${maybeId}`, body, { params: { catalogue } });
  };

  // Put a dataset back to draft
  const putDatasetToDraft = ({ id, catalogue }: { id: string, catalogue: string }) => {
    const maybeId = id ? `/${id}` : '';
    return api.put(`/datasets/hide${maybeId}`, null, { params: { catalogue } });
  };

  return {
    createDatasetDraft,
    getAllDatasetDrafts,
    getDatasetDrafts,
    deleteDatasetDraft,
    publishDatasetDraft,
    putDatasetToDraft,
  };
};

export default createDraftApi;
