// Laravel GCS Storage API - JavaScript/Node.js Example
// Install dependencies: npm install axios form-data

const axios = require('axios');
const FormData = require('form-data');
const fs = require('fs');

const BASE_URL = 'http://localhost:8000/api';

class GCSStorageClient {
    constructor(baseUrl = BASE_URL) {
        this.baseUrl = baseUrl;
        this.token = null;
        this.axios = axios.create({
            baseURL: baseUrl,
            headers: {
                'Content-Type': 'application/json',
            },
        });

        // Add request interceptor to include token
        this.axios.interceptors.request.use((config) => {
            if (this.token) {
                config.headers.Authorization = `Bearer ${this.token}`;
            }
            return config;
        });
    }

    async register(name, email, password) {
        try {
            const response = await this.axios.post('/auth/register', {
                name,
                email,
                password,
                password_confirmation: password,
            });
            
            this.token = response.data.data.access_token;
            return response.data;
        } catch (error) {
            console.error('Registration failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async login(email, password) {
        try {
            const response = await this.axios.post('/auth/login', {
                email,
                password,
            });
            
            this.token = response.data.data.access_token;
            return response.data;
        } catch (error) {
            console.error('Login failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async getCurrentUser() {
        try {
            const response = await this.axios.get('/auth/user');
            return response.data;
        } catch (error) {
            console.error('Get user failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async logout() {
        try {
            const response = await this.axios.post('/auth/logout');
            this.token = null;
            return response.data;
        } catch (error) {
            console.error('Logout failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async uploadFile(filePath, description = null) {
        try {
            const formData = new FormData();
            formData.append('file', fs.createReadStream(filePath));
            if (description) {
                formData.append('description', description);
            }

            const response = await this.axios.post('/internal/files', formData, {
                headers: {
                    ...formData.getHeaders(),
                },
            });
            
            return response.data;
        } catch (error) {
            console.error('File upload failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async listUserFiles(search = null) {
        try {
            const params = search ? { search } : {};
            const response = await this.axios.get('/internal/files', { params });
            return response.data;
        } catch (error) {
            console.error('List files failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async listPublicFiles(search = null) {
        try {
            const params = search ? { search } : {};
            const response = await this.axios.get('/public/files', { params });
            return response.data;
        } catch (error) {
            console.error('List public files failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async getFile(fileId, isPublic = false) {
        try {
            const endpoint = isPublic ? `/public/files/${fileId}` : `/internal/files/${fileId}`;
            const response = await this.axios.get(endpoint);
            return response.data;
        } catch (error) {
            console.error('Get file failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async updateFile(fileId, description = null, isPublished = null) {
        try {
            const data = {};
            if (description !== null) data.description = description;
            if (isPublished !== null) data.is_published = isPublished;

            const response = await this.axios.put(`/internal/files/${fileId}`, data);
            return response.data;
        } catch (error) {
            console.error('Update file failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async togglePublication(fileId) {
        try {
            const response = await this.axios.post(`/internal/files/${fileId}/toggle-publication`);
            return response.data;
        } catch (error) {
            console.error('Toggle publication failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async deleteFile(fileId) {
        try {
            const response = await this.axios.delete(`/internal/files/${fileId}`);
            return response.data;
        } catch (error) {
            console.error('Delete file failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async downloadFile(fileId, outputPath, isPublic = false) {
        try {
            const endpoint = isPublic ? `/public/files/${fileId}/download` : `/internal/files/${fileId}/download`;
            const response = await this.axios.get(endpoint, {
                responseType: 'stream',
            });

            const writer = fs.createWriteStream(outputPath);
            response.data.pipe(writer);

            return new Promise((resolve, reject) => {
                writer.on('finish', resolve);
                writer.on('error', reject);
            });
        } catch (error) {
            console.error('Download file failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async getUserStats() {
        try {
            const response = await this.axios.get('/internal/files/stats');
            return response.data;
        } catch (error) {
            console.error('Get user stats failed:', error.response?.data || error.message);
            throw error;
        }
    }

    async getPublicStats() {
        try {
            const response = await this.axios.get('/public/stats');
            return response.data;
        } catch (error) {
            console.error('Get public stats failed:', error.response?.data || error.message);
            throw error;
        }
    }
}

// Example usage
async function example() {
    const client = new GCSStorageClient();

    try {
        console.log('=== Laravel GCS Storage API - JavaScript Example ===\n');

        // Register or login
        console.log('1. Registering user...');
        try {
            const registerResult = await client.register('Test User', 'test@example.com', 'password123');
            console.log('Registration successful:', registerResult.message);
        } catch (error) {
            console.log('Registration failed, trying login...');
            const loginResult = await client.login('test@example.com', 'password123');
            console.log('Login successful:', loginResult.message);
        }

        // Get current user
        console.log('\n2. Getting current user...');
        const user = await client.getCurrentUser();
        console.log('Current user:', user.data.name);

        // List files (should be empty initially)
        console.log('\n3. Listing user files...');
        const userFiles = await client.listUserFiles();
        console.log('User files count:', userFiles.data.length);

        // List public files
        console.log('\n4. Listing public files...');
        const publicFiles = await client.listPublicFiles();
        console.log('Public files count:', publicFiles.data.length);

        // Get stats
        console.log('\n5. Getting statistics...');
        const userStats = await client.getUserStats();
        console.log('User stats:', userStats.data);

        const publicStats = await client.getPublicStats();
        console.log('Public stats:', publicStats.data);

        // File upload example (commented out - requires actual file)
        /*
        console.log('\n6. Uploading file...');
        const uploadResult = await client.uploadFile('./sample.txt', 'Sample file description');
        console.log('Upload successful:', uploadResult.message);

        const fileId = uploadResult.data.id;

        // Update file
        console.log('\n7. Updating file...');
        const updateResult = await client.updateFile(fileId, 'Updated description', true);
        console.log('Update successful:', updateResult.message);

        // Download file
        console.log('\n8. Downloading file...');
        await client.downloadFile(fileId, './downloaded_file.txt');
        console.log('Download successful');

        // Delete file
        console.log('\n9. Deleting file...');
        const deleteResult = await client.deleteFile(fileId);
        console.log('Delete successful:', deleteResult.message);
        */

        // Logout
        console.log('\n6. Logging out...');
        const logoutResult = await client.logout();
        console.log('Logout successful:', logoutResult.message);

        console.log('\n=== Example completed successfully ===');

    } catch (error) {
        console.error('Example failed:', error.message);
    }
}

// Run example if this file is executed directly
if (require.main === module) {
    example();
}

module.exports = GCSStorageClient;