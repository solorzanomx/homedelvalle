' =============================================
' SessionHelper.vb
' Maneja la sesión del usuario autenticado
' Almacena y recupera datos del usuario de HttpSession
' =============================================
Imports System.Web

Namespace CRM_VBNet.Helpers

    Public Class SessionHelper

        Private Const USER_KEY As String = "CurrentUser"
        Private Const ROLE_KEY As String = "UserRole"
        Private Const USER_ID_KEY As String = "UserId"

        ''' <summary>
        ''' Inicia sesión almacenando el usuario en HttpSession
        ''' </summary>
        Public Shared Sub Login(user As Models.User)
            HttpContext.Current.Session(USER_KEY) = user
            HttpContext.Current.Session(ROLE_KEY) = user.Role
            HttpContext.Current.Session(USER_ID_KEY) = user.Id
        End Sub

        ''' <summary>
        ''' Cierra sesión limpiando la sesión
        ''' </summary>
        Public Shared Sub Logout()
            HttpContext.Current.Session.Clear()
            HttpContext.Current.Session.Abandon()
        End Sub

        ''' <summary>
        ''' Verifica si hay un usuario autenticado
        ''' </summary>
        Public Shared Function IsAuthenticated() As Boolean
            Return HttpContext.Current?.Session(USER_KEY) IsNot Nothing
        End Function

        ''' <summary>
        ''' Obtiene el usuario actual de la sesión
        ''' </summary>
        Public Shared Function GetCurrentUser() As Models.User
            If Not IsAuthenticated() Then Return Nothing
            Return TryCast(HttpContext.Current.Session(USER_KEY), Models.User)
        End Function

        ''' <summary>
        ''' Obtiene el ID del usuario actual
        ''' </summary>
        Public Shared Function GetCurrentUserId() As Integer
            If Not IsAuthenticated() Then Return 0
            Return CInt(HttpContext.Current.Session(USER_ID_KEY))
        End Function

        ''' <summary>
        ''' Obtiene el rol del usuario actual
        ''' </summary>
        Public Shared Function GetCurrentRole() As Models.Role
            If Not IsAuthenticated() Then Return Nothing
            Return TryCast(HttpContext.Current.Session(ROLE_KEY), Models.Role)
        End Function

        ''' <summary>
        ''' Actualiza los datos del usuario en sesión (después de editar perfil)
        ''' </summary>
        Public Shared Sub RefreshUser(user As Models.User)
            HttpContext.Current.Session(USER_KEY) = user
        End Sub

    End Class

End Namespace
