import { get, post, put, del } from './apiClient'

export const API_BASE_URL = '/api'

export class TeacherDashboardAPI {
  // Class Configuration endpoints
  static classConfigurations = {
    list: () => get<any[]>(`${API_BASE_URL}/teacher/class-configurations`),
    create: (data: CreateClassConfigurationRequest) =>
      post<ClassConfiguration>(`${API_BASE_URL}/teacher/class-configurations`, data),
    update: (id: number, data: UpdateClassConfigurationRequest) =>
      put<ClassConfiguration>(`${API_BASE_URL}/teacher/class-configurations/${id}`, data),
    delete: (id: number) =>
      del(`${API_BASE_URL}/teacher/class-configurations/${id}`),
  }

  // Activity History endpoints
  static activityHistory = {
    list: (params?: ActivityHistoryParams) =>
      get<ActivityHistoryResponse>(`${API_BASE_URL}/teacher/activity-history`, { params }),
    create: (data: CreateActivityRequest) =>
      post<TeacherActivity>(`${API_BASE_URL}/teacher/activity-history`, data),
    update: (id: number, data: UpdateActivityRequest) =>
      put<TeacherActivity>(`${API_BASE_URL}/teacher/activity-history/${id}`, data),
    delete: (id: number) =>
      del(`${API_BASE_URL}/teacher/activity-history/${id}`),
  }

  // Dashboard endpoints
  static dashboard = {
    stats: (params?: DashboardStatsParams) =>
      get<DashboardStats>(`${API_BASE_URL}/teacher/dashboard/stats`, { params }),
    recentActivities: () =>
      get<TeacherActivity[]>(`${API_BASE_URL}/teacher/dashboard/recent-activities`),
    topQuizzes: () =>
      get<any[]>(`${API_BASE_URL}/teacher/dashboard/top-quizzes`),
  }

  // Supporting APIs
  static teachers = {
    list: () => get<Teacher[]>(`${API_BASE_URL}/teachers`),
    get: (id: number) => get<Teacher>(`${API_BASE_URL}/teachers/${id}`),
  }

  static activityTypes = {
    list: () => get<ActivityType[]>(`${API_BASE_URL}/activity-types`),
  }
}
